<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| User Channel (Private notifications, friend requests)
|--------------------------------------------------------------------------
*/
Broadcast::channel('user.{id}', function ($user, $id) {
    $result = (int) $user->id === (int) $id;
    \Log::info('Channel Auth: user.{id}', ['user_id' => $user->id, 'target_id' => $id, 'result' => $result]);
    return $result;
});

/*
|--------------------------------------------------------------------------
| Admin Guest Chats Channel (New session alerts)
|--------------------------------------------------------------------------
*/
Broadcast::channel('admin.guest-chats', function ($user) {
    $adminRoles = ['admin', 'manager', 'super_admin'];
    return $user->roles()->whereIn('name', $adminRoles)->exists() || 
           (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($adminRoles));
});

/*
|--------------------------------------------------------------------------
| Conversation Channel (Private messages)
|--------------------------------------------------------------------------
*/
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    if (!$conversation) {
        \Log::warning('Channel Auth: conversation.{id} NOT FOUND', ['conversation_id' => $conversationId]);
        return false;
    }

    // Check if it's a guest chat (direct attribute or relation)
    $isGuest = $conversation->is_guest || $conversation->guestSession()->exists();

    if ($isGuest) {
        // For guest chats, allow all admin/manager/super_admin to listen even if not assigned
        $adminRoles = ['admin', 'manager', 'super_admin'];
        $isAdmin = $user->roles()->whereIn('name', $adminRoles)->exists() || 
                   (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($adminRoles));

        if ($isAdmin) {
            \Log::info('Channel Auth: conversation.{id} (GUEST) ALLOWED for Admin', ['user_id' => $user->id, 'conversation_id' => $conversationId]);
            return true;
        }
    }

    // Normal private/group chat logic: must be a member
    $result = $conversation->users()->where('user_id', $user->id)->exists();
    \Log::info('Channel Auth: conversation.{id}', ['user_id' => $user->id, 'conversation_id' => $conversationId, 'result' => $result]);
    return $result;
});

/*
|--------------------------------------------------------------------------
| Presence Channel for Online Status in Conversation
|--------------------------------------------------------------------------
*/
Broadcast::channel('presence.conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    if (!$conversation) {
        return false;
    }
    if ($conversation->users()->where('user_id', $user->id)->exists()) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar,
        ];
    }
    return false;
});

// Legacy channel for backward compatibility
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

