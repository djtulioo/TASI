<?php
// Carregar autoloader do Composer
require __DIR__ . '/vendor/autoload.php';

// Inicializar a aplicação Laravel
$app = require __DIR__ . '/bootstrap/app.php';

// Criar kernel HTTP para inicializar componentes (opcional, mas bom para garantir Facades)
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Channel;

try {
    $channels = Channel::all();
    $output = "Total Channels: " . $channels->count() . "\n";

    foreach ($channels as $channel) {
        $output .= "ID: {$channel->id} | Name: {$channel->name} | Team ID: {$channel->team_id} | Type: {$channel->type}\n";
        $output .= "   Phone ID: " . ($channel->phone_number_id ? "'{$channel->phone_number_id}'" : "NULL") . "\n";
        $output .= "   Telegram Token: " . ($channel->telegram_bot_token ? "SET" : "NULL") . "\n";
        
        $related = $channel->sameBotChannelIds();
        $output .= "   Related IDs: " . implode(', ', $related) . "\n";
        
        $convCount = \App\Models\Conversation::where('channel_id', $channel->id)->count();
        $output .= "   Conversations: " . $convCount . "\n";

        $output .= "--------------------------------------------------\n";
    }
    file_put_contents('debug_output.txt', $output);
    echo "Output written to debug_output.txt";

    $distinctIds = \App\Models\Conversation::distinct()->pluck('channel_id');
    $orphanedOutput = "\nDistinct Channel IDs in Conversations: " . $distinctIds->implode(', ') . "\n";
    
    $summariesCount = \DB::table('daily_summaries')->count();
    $orphanedOutput .= "Daily Summaries Count: " . $summariesCount . "\n";
    
    file_put_contents('debug_output.txt', $orphanedOutput, FILE_APPEND);
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
