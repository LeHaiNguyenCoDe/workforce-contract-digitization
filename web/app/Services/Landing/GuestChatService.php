<?php

namespace App\Services\Landing;

use App\Models\Conversation;
use App\Models\GuestChatSession;
use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;
use App\Events\GuestChatStarted;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class GuestChatService
{
    /**
     * Start a new guest chat session.
     */
    public function startSession(string $name, string $contact, string $contactType): GuestChatSession
    {
        return DB::transaction(function () use ($name, $contact, $contactType) {
            // Find available support staff
            $staffId = $this->findAvailableStaff();

            // Ensure we have a valid created_by user
            $createdById = $staffId ?? User::first()?->id;
            if (!$createdById) {
                throw new \Exception('No staff available to handle chat');
            }

            // Create a conversation
            $conversation = Conversation::create([
                'name' => "Guest: {$name}",
                'type' => 'private',
                'created_by' => $createdById,
            ]);

            // Add staff to conversation if found
            if ($staffId) {
                $conversation->users()->attach($staffId, ['role' => 'admin']);
            }

            // Create guest session
            $session = GuestChatSession::create([
                'session_token' => $this->generateToken(),
                'guest_name' => $name,
                'guest_contact' => $contact,
                'contact_type' => $contactType,
                'conversation_id' => $conversation->id,
                'assigned_staff_id' => $staffId,
                'status' => 'active',
                'last_activity_at' => now(),
            ]);

            // Send system message
            $this->sendSystemMessage($conversation->id, "Khách hàng {$name} đã bắt đầu cuộc trò chuyện.");

            // Broadcast new guest chat for admins
            event(new GuestChatStarted($conversation));

            return $session;
        });
    }

    /**
     * Get messages for a session.
     */
    public function getMessages(string $sessionToken): array
    {
        $session = $this->getSession($sessionToken);

        if (!$session) {
            throw new \Exception('Session not found or expired');
        }

        $messages = Message::where('conversation_id', $session->conversation_id)
            ->with(['attachments', 'user'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'type' => $message->type,
                    'is_guest' => $message->metadata['is_guest'] ?? false,
                    'created_at' => $message->created_at->toIso8601String(),
                    'staff_name' => $message->metadata['staff_name'] ?? ($message->user?->name ?? 'Nhân viên'),
                    'staff_avatar' => $message->user?->avatar ? asset('storage/' . $message->user->avatar) : null,
                    'metadata' => $message->metadata,
                    'attachments' => $message->attachments->map(fn($a) => [
                        'id' => $a->id,
                        'file_name' => $a->file_name,
                        'file_path' => asset('storage/' . $a->file_path),
                        'file_type' => $a->file_type,
                        'thumbnail_path' => $a->thumbnail_path ? asset('storage/' . $a->thumbnail_path) : null,
                    ]),
                ];
            });

        return [
            'session' => [
                'token' => $session->session_token,
                'guest_name' => $session->guest_name,
                'status' => $session->status,
                'assigned_staff' => $session->assignedStaff ? [
                    'id' => $session->assignedStaff->id,
                    'name' => $session->assignedStaff->name,
                    'avatar' => $session->assignedStaff->avatar ? asset('storage/' . $session->assignedStaff->avatar) : null,
                ] : null,
            ],
            'messages' => $messages->toArray(),
        ];
    }

    /**
     * Assign staff to session.
     */
    public function assignStaff(string $sessionToken, int $staffId): array
    {
        $session = $this->getSession($sessionToken);
        if (!$session) {
            throw new \Exception('Session not found or expired');
        }

        $staff = User::findOrFail($staffId);

        // Update session
        $session->update([
            'assigned_staff_id' => $staffId,
        ]);

        // Add staff to conversation if not already there
        $conversation = $session->conversation;
        if (!$conversation->users()->where('user_id', $staffId)->exists()) {
            $conversation->users()->attach($staffId, ['role' => 'member']);
        }

        // Broadcast session updated event (using generic MessageSent or similar if needed)
        // For waiting state, polling will catch the updated assigned_staff

        return [
            'id' => $staff->id,
            'name' => $staff->name,
            'avatar' => $staff->avatar ? asset('storage/' . $staff->avatar) : null,
        ];
    }

    /**
     * Send a message from guest.
     */
    public function sendMessage(string $sessionToken, string $content, ?array $attachments = null): array
    {
        $session = $this->getSession($sessionToken);

        if (!$session) {
            throw new \Exception('Session not found or expired');
        }

        $result = DB::transaction(function () use ($session, $content, $attachments) {
            // Create message
            $message = Message::create([
                'conversation_id' => $session->conversation_id,
                'user_id' => null,
                'content' => $content,
                'type' => !empty($attachments) ? 'image' : 'text',
                'metadata' => [
                    'is_guest' => true,
                    'guest_name' => $session->guest_name,
                    'session_id' => $session->id,
                ],
            ]);

            // Add attachments if any
            if ($attachments) {
                foreach ($attachments as $file) {
                    $path = $file->store('chat/attachments', 'public');
                    $message->attachments()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            // Update session activity
            $session->update(['last_activity_at' => now()]);

            // Broadcast to staff
            broadcast(new MessageSent($message->load(['user', 'attachments'])))->toOthers();

            // Check for bot response - MOVED outside transaction
            // if (empty($attachments)) {
            //     $this->processBotResponse($session->conversation, $content);
            // }

            // Explicitly reload attachments to ensure they are available
            $message->load('attachments');

            return $result = [
                'id' => $message->id,
                'content' => $message->content,
                'is_guest' => true,
                'type' => $message->type,
                'created_at' => $message->created_at->toIso8601String(),
                'attachments' => $message->attachments->map(fn($a) => [
                    'id' => $a->id,
                    'file_name' => $a->file_name,
                    'file_path' => asset('storage/' . $a->file_path),
                    'file_type' => $a->file_type,
                ]),
            ];
        });

        // Check for bot response OUTSIDE transaction to prevent rollback of user message
        if (empty($attachments)) {
            try {
                $this->processBotResponse($session->conversation, $content);
            } catch (\Exception $e) {
                // Log bot error but don't fail user message
                // Log::error('Bot response failed: ' . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Process bot response based on content.
     */
    private function processBotResponse(Conversation $conversation, string $content): void
    {
        $normalized = mb_strtolower($content);
        $response = null;
        $productId = null;
        $shouldHandover = false;

        // 0. Explicit Handover
        // Keywords: 'nhân viên', 'tư vấn viên', 'gặp', 'chat với người', 'liên hệ'
        if (Str::contains($normalized, ['nhân viên', 'tư vấn viên', 'gặp người', 'chat với người', 'tư vấn trực tiếp', 'liên hệ'])) {
            $response = "Tôi sẽ chuyển cuộc hội thoại đến nhân viên hỗ trợ để giải đáp cho bạn ngay nhé!";
            $shouldHandover = true;
        }

        // 1. Availability / Stock
        if (!$response && Str::contains($normalized, ['còn hàng', 'số lượng', 'có sẵn'])) {
            $product = $this->identifyProduct($normalized);
            if ($product) {
                $productId = $product->id;
                // Get total stock from variants
                $stock = ProductVariant::where('product_id', $product->id)->sum('stock');
                if ($stock > 0) {
                    $response = "Vâng, sản phẩm **{$product->name}** hiện đang còn hàng ạ! (Số lượng còn lại: {$stock}). Bạn có muốn đặt hàng ngay không?";
                } else {
                    $response = "Rất tiếc, sản phẩm **{$product->name}** hiện đang tạm hết hàng. Bạn có muốn mình thông báo khi có hàng trở lại không?";
                }
            } else {
                $response = "Hiện tại sản phẩm bạn đang kiếm chưa cập nhật mới. Cảm ơn bạn đã quan tâm, chúng tôi sẽ cập nhật trong thời gian sớm nhất.";
            }
        }

        // 2. Price
        if (!$response && Str::contains($normalized, ['giá', 'bao nhiêu', 'giá cả'])) {
            $product = $this->identifyProduct($normalized);
            if ($product) {
                $productId = $product->id;
                $formattedPrice = number_format($product->price, 0, ',', '.') . '₫';
                $response = "Sản phẩm **{$product->name}** hiện có giá là **{$formattedPrice}**. Bạn có muốn xem thêm chi tiết không?";
            } else {
                $response = "Hiện tại sản phẩm bạn đang kiếm chưa cập nhật mới. Cảm ơn bạn đã quan tâm, chúng tôi sẽ cập nhật trong thời gian sớm nhất.";
            }
        }

        // 3. Shipping
        if (!$response && Str::contains($normalized, ['ship', 'vận chuyển', 'giao hàng'])) {
            $response = "Bên mình hiện đang có chương trình **Miễn phí vận chuyển** cho tất cả đơn hàng! Thời gian giao hàng dự kiến từ 2-4 ngày làm việc ạ.";
        }

        // 4. Support
        if (!$response && Str::contains($normalized, ['hỗ trợ', 'chính sách', 'đổi trả'])) {
            $response = "Bên mình hỗ trợ khách hàng **24/7**! Bạn có thể đổi trả sản phẩm trong vòng **30 ngày** nếu có lỗi từ nhà sản xuất. Bạn cần mình giúp gì thêm không?";
        }

        // 5. Positive/Affirmation (Navigation trigger)
        if (!$response && Str::contains($normalized, ['có', 'ok', 'yes', 'yep', 'ừ', 'uh', 'xem', 'chi tiết', 'muốn'])) {
            // Frontend generic affirmation - prevent fallback error
            $response = "Vâng, mời bạn xem chi tiết ạ!";
        }

        // 6. Fallback
        if (!$response) {
            $response = "Xin lỗi, hiện tại tôi chưa có thông tin cụ thể về vấn đề này. Tôi sẽ chuyển cuộc hội thoại đến nhân viên hỗ trợ để giải đáp cho bạn ngay nhé!";
            $shouldHandover = true;
        }

        if ($response) {
            Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => null,
                'content' => $response,
                'type' => 'text',
                'metadata' => [
                    'is_guest' => false,
                    'staff_name' => 'Trợ lý ảo',
                    'is_bot' => true,
                    'product_id' => $productId
                ],
            ]);
        }

        // Update status AFTER sending message to prevent blocking
        if ($shouldHandover) {
            try {
                GuestChatSession::where('conversation_id', $conversation->id)
                    ->update(['status' => 'waiting_for_staff']);
            } catch (\Exception $e) {
                // Log error but allow message flow to continue
                // Log::error('Failed to update session status: ' . $e->getMessage());
            }
        }
    }

    /**
     * Identify product by name in content.
     */
    private function identifyProduct(string $content): ?Product
    {
        // Simple name matching
        $products = Product::all();
        foreach ($products as $product) {
            if (Str::contains($content, mb_strtolower($product->name))) {
                return $product;
            }
        }
        return null;
    }

    /**
     * Get session info including staff.
     */
    public function getSessionInfo(string $sessionToken): array
    {
        $session = $this->getSession($sessionToken);
        if (!$session) {
            throw new \Exception('Session not found');
        }

        $staff = $session->assignedStaff;

        return [
            'guest_name' => $session->guest_name,
            'staff' => $staff ? [
                'id' => $staff->id,
                'name' => $staff->name,
                'avatar' => $staff->avatar ? asset('storage/' . $staff->avatar) : null,
            ] : null,
        ];
    }

    /**
     * Get session by token.
     */
    public function getSession(string $sessionToken): ?GuestChatSession
    {
        return GuestChatSession::where('session_token', $sessionToken)
            ->active()
            ->first();
    }

    /**
     * Check session status.
     */
    public function getSessionStatus(string $sessionToken): array
    {
        $session = GuestChatSession::where('session_token', $sessionToken)->first();

        return [
            'active' => $session ? $session->isActive() : false,
        ];
    }

    /**
     * Find available support staff.
     * Simplified: just return first admin user or user ID 1
     */
    private function findAvailableStaff(): ?int
    {
        // Simple approach: get first available user (admin)
        $staff = User::where('id', 1)->first();

        return $staff?->id;
    }

    /**
     * Generate unique session token.
     */
    private function generateToken(): string
    {
        do {
            $token = Str::random(64);
        } while (GuestChatSession::where('session_token', $token)->exists());

        return $token;
    }

    /**
     * Send system message.
     */
    private function sendSystemMessage(int $conversationId, string $content): void
    {
        Message::create([
            'conversation_id' => $conversationId,
            'user_id' => null,
            'content' => $content,
            'type' => 'system',
            'metadata' => ['is_system' => true],
        ]);
    }
}
