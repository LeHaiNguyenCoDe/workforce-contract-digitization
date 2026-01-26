<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Broadcasting Authentication Controller
 *
 * Handles WebSocket channel authorization for Laravel Reverb broadcasting.
 * Determines which users can subscribe to private and presence channels.
 */
class BroadcastingController extends Controller
{
    /**
     * Authenticate the request for broadcasting.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(Request $request)
    {
        $user = $request->user();
        $channelName = $request->channel_name;

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $normalizedName = $this->normalizeChannelName($channelName);
        $isPresence = str_starts_with($channelName, 'presence-');
        $presenceData = null;

        $authorized = $this->isAuthorized($user, $normalizedName, $isPresence, $presenceData);

        if ($authorized) {
            return $this->generateAuthResponse($request, $channelName, $isPresence, $presenceData);
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }

    /**
     * Normalize channel name by removing prefix.
     *
     * @param string $channelName
     * @return string
     */
    private function normalizeChannelName(string $channelName): string
    {
        if (str_starts_with($channelName, 'private-')) {
            return substr($channelName, 8);
        }

        if (str_starts_with($channelName, 'presence-')) {
            return substr($channelName, 9);
        }

        return $channelName;
    }

    /**
     * Check if user is authorized to access the channel.
     *
     * @param mixed $user
     * @param string $normalizedName
     * @param bool $isPresence
     * @param array|null $presenceData
     * @return bool
     */
    private function isAuthorized($user, string $normalizedName, bool $isPresence, &$presenceData): bool
    {
        // User private channel - only the user themselves can access
        if (preg_match('/^user\.(\d+)$/', $normalizedName, $matches)) {
            return (int) $user->id === (int) $matches[1];
        }

        // Conversation channel - check if user is a member
        if (preg_match('/^conversation\.(\d+)$/', $normalizedName, $matches)) {
            return $this->canAccessConversation($user->id, (int) $matches[1]);
        }

        // Presence conversation channel - check membership and set presence data
        if (preg_match('/^presence\.conversation\.(\d+)$/', $normalizedName, $matches)) {
            $conversationId = (int) $matches[1];
            $authorized = $this->canAccessConversation($user->id, $conversationId);

            if ($authorized) {
                $presenceData = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                ];
            }

            return $authorized;
        }

        // Admin guest chats channel - only admins can access
        if ($normalizedName === 'admin.guest-chats') {
            return $this->isAdmin($user);
        }

        return false;
    }

    /**
     * Check if user can access a conversation.
     *
     * @param int $userId
     * @param int $conversationId
     * @return bool
     */
    private function canAccessConversation(int $userId, int $conversationId): bool
    {
        return DB::table('conversation_user')
            ->where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Check if user has admin privileges.
     *
     * @param mixed $user
     * @return bool
     */
    private function isAdmin($user): bool
    {
        $adminRoles = ['admin', 'manager', 'super_admin'];
        return in_array($user->role, $adminRoles);
    }

    /**
     * Generate authentication response for broadcasting.
     *
     * @param Request $request
     * @param string $channelName
     * @param bool $isPresence
     * @param array|null $presenceData
     * @return \Illuminate\Http\JsonResponse
     */
    private function generateAuthResponse(Request $request, string $channelName, bool $isPresence, ?array $presenceData)
    {
        $socketId = $request->socket_id;
        $appKey = config('broadcasting.connections.reverb.key');
        $appSecret = config('broadcasting.connections.reverb.secret');

        $stringToSign = $socketId . ':' . $channelName;

        if ($isPresence && $presenceData) {
            $channelData = json_encode([
                'user_id' => (string) $presenceData['id'],
                'user_info' => $presenceData
            ]);
            $stringToSign .= ':' . $channelData;
        }

        $signature = hash_hmac('sha256', $stringToSign, $appSecret);
        $response = ['auth' => $appKey . ':' . $signature];

        if ($isPresence && $presenceData) {
            $response['channel_data'] = $channelData;
        }

        return response()->json($response);
    }
}
