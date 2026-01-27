<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Landing\GuestChatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GuestChatController extends Controller
{
    public function __construct(
        private GuestChatService $guestChatService
    ) {
    }

    /**
     * Start a new guest chat session.
     */
    public function startSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'contact' => 'required|string|max:100',
            'contact_type' => 'required|in:email,phone',
        ]);

        try {
            $session = $this->guestChatService->startSession(
                $validated['name'],
                $validated['contact'],
                $validated['contact_type']
            );

            return response()->json([
                'status' => 'success',
                'data' => [
                    'session_token' => $session->session_token,
                    'conversation_id' => $session->conversation_id,
                ],
            ], 201);
        } catch (\Exception $e) {
            \Log::error('GuestChat startSession error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể tạo phiên chat. Vui lòng thử lại.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get messages for a session.
     */
    public function getMessages(string $sessionToken): JsonResponse
    {
        try {
            $messages = $this->guestChatService->getMessages($sessionToken);

            return response()->json([
                'status' => 'success',
                'data' => $messages,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Phiên chat không tồn tại hoặc đã hết hạn.',
            ], 404);
        }
    }

    /**
     * Send a message.
     */
    public function sendMessage(Request $request, string $sessionToken): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'nullable|string|max:2000',
            'attachments.*' => 'nullable|image|max:10240', // Max 10MB per image
        ]);

        try {
            $message = $this->guestChatService->sendMessage(
                $sessionToken,
                $validated['content'] ?? '',
                $request->file('attachments')
            );

            return response()->json([
                'status' => 'success',
                'data' => $message,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể gửi tin nhắn. Vui lòng thử lại.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get session info.
     */
    public function getSessionInfo(string $sessionToken): JsonResponse
    {
        try {
            $info = $this->guestChatService->getSessionInfo($sessionToken);

            return response()->json([
                'status' => 'success',
                'data' => $info,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Phiên chat không tồn tại hoặc đã hết hạn.',
            ], 404);
        }
    }

    /**
     * Get session status.
     */
    public function getStatus(string $sessionToken): JsonResponse
    {
        $status = $this->guestChatService->getSessionStatus($sessionToken);

        return response()->json([
            'status' => 'success',
            'data' => $status,
        ]);
    }
    /**
     * Assign staff to a guest chat session.
     */
    public function assignStaff(Request $request, string $sessionToken): JsonResponse
    {
        $request->validate([
            'staff_id' => 'required|integer|exists:users,id',
        ]);

        try {
            $staff = $this->guestChatService->assignStaff($sessionToken, $request->input('staff_id'));
            return response()->json([
                'status' => 'success',
                'data' => $staff,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
