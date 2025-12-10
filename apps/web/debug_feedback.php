<?php

use App\Models\FeedbackEntry;
use App\Models\Conversation;
use App\Models\Channel;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Recent Feedback Entries ---\n";
$entries = FeedbackEntry::latest()->take(5)->get();
if ($entries->isEmpty()) {
    echo "No feedback entries found.\n";
} else {
    foreach ($entries as $entry) {
        echo "ID: {$entry->id} | Channel ID: {$entry->channel_id} | Bot User ID: " . ($entry->bot_user_id ?? 'NULL') . " | Title: {$entry->titulo}\n";
    }
}

echo "\n--- Recent Conversations ---\n";
$conversations = Conversation::latest()->take(5)->get();
foreach ($conversations as $conv) {
    echo "ID: {$conv->id} | Channel ID: {$conv->channel_id} | Bot User ID: " . ($conv->bot_user_id ?? 'NULL') . " | Dir: {$conv->direction}\n";
}
