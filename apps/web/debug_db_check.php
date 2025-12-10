<?php

use App\Models\FeedbackEntry;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Recent Feedback Entries ---\n";
$entries = FeedbackEntry::orderBy('id', 'desc')->get(); // Get ALL, ordered by latest
if ($entries->isEmpty()) {
    echo "No feedback entries found.\n";
} else {
    foreach ($entries as $entry) {
        echo "ID: {$entry->id} | Title: {$entry->titulo} | Created: {$entry->created_at}\n";
    }
}
