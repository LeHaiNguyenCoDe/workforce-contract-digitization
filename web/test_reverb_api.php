<?php

// Test direct HTTP to Reverb API
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$config = config('broadcasting.connections.reverb');
$reverbConfig = config('reverb.apps.apps.0');

echo "Broadcasting config:\n";
echo "  host: " . $config['options']['host'] . "\n";
echo "  port: " . $config['options']['port'] . "\n";
echo "  scheme: " . $config['options']['scheme'] . "\n";
echo "  key: " . $config['key'] . "\n";
echo "  secret: " . substr($config['secret'], 0, 10) . "...\n";
echo "  app_id: " . $config['app_id'] . "\n";

$baseUrl = $config['options']['scheme'] . '://' . $config['options']['host'] . ':' . $config['options']['port'];
echo "\nBase URL: $baseUrl\n";

// Try to send a test event
$channel = 'private-user.3';
$event = 'message.sent';
$data = json_encode(['test' => 'hello from PHP']);

$body = [
    'name' => $event,
    'channel' => $channel,
    'data' => $data,
];

// Generate auth signature like Pusher expects
$bodyMd5 = md5(json_encode($body));
$timestamp = time();
$string_to_sign = "POST\n/apps/{$config['app_id']}/events\nauth_key={$config['key']}&auth_timestamp=$timestamp&auth_version=1.0&body_md5=$bodyMd5";
$auth_signature = hash_hmac('sha256', $string_to_sign, $config['secret']);

$url = "$baseUrl/apps/{$config['app_id']}/events?auth_key={$config['key']}&auth_timestamp=$timestamp&auth_version=1.0&body_md5=$bodyMd5&auth_signature=$auth_signature";

echo "\nSending test event to: $url\n";
echo "Body: " . json_encode($body) . "\n";

try {
    $response = Http::withBody(json_encode($body), 'application/json')
        ->post($url);
    
    echo "\nResponse Status: " . $response->status() . "\n";
    echo "Response Body: " . $response->body() . "\n";
} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
}
