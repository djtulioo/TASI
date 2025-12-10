<?php

use App\Models\FeedbackEntry;
use App\Models\Channel;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Running backfill for feedback_entries...\n";

$entries = FeedbackEntry::whereNull('bot_user_id')->get();
$count = 0;

foreach ($entries as $entry) {
    if ($entry->channel_id) {
        $channel = Channel::find($entry->channel_id);
        if ($channel) {
            $botId = $channel->phone_number_id ?? $channel->telegram_bot_token;
            if ($botId) {
                $entry->bot_user_id = $botId;
                $entry->save();
                $count++;
                echo "Updated Entry ID: {$entry->id} with Bot ID: {$botId}\n";
            } else {
                echo "Skipping Entry ID: {$entry->id} - Channel has no bot ID\n";
            }
        } else {
            echo "Skipping Entry ID: {$entry->id} - Channel not found\n";
        }
    }
}

echo "Backfill complete. Updated {$count} entries.\n";
