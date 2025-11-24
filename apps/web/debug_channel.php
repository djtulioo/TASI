<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    $channel = \App\Models\Channel::firstOrCreate(
        ['telegram_bot_token' => 'test_token'],
        [
            'team_id' => 1,
            'name' => 'Test Channel',
            'type' => 'telegram'
        ]
    );
    echo "Channel created: " . $channel->id . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
