<?php

use App\Models\FeedbackEntry;
use App\Models\Conversation;
use App\Models\Channel;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$channelId = 11; // From previous context

echo "--- All Feedback Entries for Channel {$channelId} ---\n";
$entries = FeedbackEntry::where('channel_id', $channelId)->get();
if ($entries->isEmpty()) {
    echo "No feedback entries found.\n";
} else {
    foreach ($entries as $entry) {
        echo "ID: {$entry->id} | Created: {$entry->created_at} | Bot User ID: " . ($entry->bot_user_id ?? 'NULL') . " | Title: {$entry->titulo}\n";
    }
}

echo "\n--- Last 20 Conversations for Channel {$channelId} ---\n";
$conversations = Conversation::where('channel_id', $channelId)->orderBy('created_at', 'desc')->take(20)->get();
foreach ($conversations->reverse() as $conv) {
    echo "ID: {$conv->id} | Created: {$conv->created_at} | Sender: {$conv->sender_identifier} | Type: {$conv->direction} | BotID: " . ($conv->bot_user_id ?? 'NULL') . "\n";
    echo "Body: " . substr(str_replace("\n", " ", $conv->message_body), 0, 100) . "...\n";
    echo "--------------------------------------------------\n";
}
