<?php

// Test script to manually broadcast and see if Reverb receives it
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Events\MessageSent;
use App\Models\Message;

$message = Message::find(151);

if (!$message) {
    echo "Message 151 not found. Creating test...\n";
    exit(1);
}

echo "Broadcasting message {$message->id} to conversation {$message->conversation_id}\n";
echo "Channels: private-conversation.{$message->conversation_id}, private-user.3\n";

try {
    $result = broadcast(new MessageSent($message));
    echo "Broadcast result: " . ($result ? "SUCCESS" : "NO RESULT") . "\n";
    echo "Check Reverb terminal for debug output\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
