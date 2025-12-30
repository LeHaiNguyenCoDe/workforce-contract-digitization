<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\FriendController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Chat, Friends & Notifications API Routes
|--------------------------------------------------------------------------
*/

// Chat routes
Route::prefix('chat')->group(function () {
    Route::get('conversations', [ChatController::class, 'index']);
    Route::get('conversations/{conversationId}', [ChatController::class, 'show']);
    Route::post('conversations/private', [ChatController::class, 'startPrivateChat']);
    Route::post('conversations/group', [ChatController::class, 'createGroup']);
    Route::get('conversations/{conversationId}/messages', [ChatController::class, 'getMessages']);
    Route::post('conversations/{conversationId}/messages', [ChatController::class, 'sendMessage']);
    Route::post('conversations/{conversationId}/read', [ChatController::class, 'markAsRead']);
    Route::post('conversations/{conversationId}/members', [ChatController::class, 'addMembers']);
    Route::delete('conversations/{conversationId}/members/{userId}', [ChatController::class, 'removeMember']);
    Route::post('conversations/{conversationId}/leave', [ChatController::class, 'leave']);
    Route::delete('conversations/{conversationId}', [ChatController::class, 'deleteConversation']);
    Route::delete('messages/{messageId}', [ChatController::class, 'deleteMessage']);
});

// Friend routes
Route::prefix('friends')->group(function () {
    Route::get('/', [FriendController::class, 'index']);
    Route::get('pending', [FriendController::class, 'pending']);
    Route::get('sent', [FriendController::class, 'sent']);
    Route::post('request', [FriendController::class, 'sendRequest']);
    Route::post('{friendshipId}/accept', [FriendController::class, 'accept']);
    Route::post('{friendshipId}/reject', [FriendController::class, 'reject']);
    Route::delete('{friendshipId}/cancel', [FriendController::class, 'cancel']);
    Route::delete('{userId}/unfriend', [FriendController::class, 'unfriend']);
    Route::post('{userId}/block', [FriendController::class, 'block']);
    Route::get('search', [FriendController::class, 'search']);
});

// Notification routes
Route::prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('{notificationId}/read', [NotificationController::class, 'markAsRead']);
    Route::post('read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('{notificationId}', [NotificationController::class, 'destroy']);
    Route::delete('/', [NotificationController::class, 'destroyAll']);
});
