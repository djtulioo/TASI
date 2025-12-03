<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Channel;
use App\Services\TelegramService;

class ResetTelegramWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-telegram-webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets the Telegram bot webhook to the specified URL';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegramService)
    {
        $channels = Channel::where('type', 'telegram')->get();

        if ($channels->isEmpty()) {
            $this->error('No Telegram channels found.');
            return;
        }

        $this->info('Available Telegram Channels:');
        foreach ($channels as $channel) {
            $this->line("[{$channel->id}] {$channel->name} (Team ID: {$channel->team_id})");
        }

        $channelId = $this->ask('Enter the Channel ID to configure');
        $channel = $channels->find($channelId);

        if (!$channel) {
            $this->error('Channel not found.');
            return;
        }

        $baseUrl = $this->ask('Enter the Base URL (e.g., https://your-ngrok-url.ngrok-free.app)');
        
        // Remove trailing slash if present
        $baseUrl = rtrim($baseUrl, '/');

        // Construct the full webhook URL based on routes/api.php
        // Route::post('/webhook/telegram/{bot_token}', ...)
        $webhookUrl = "{$baseUrl}/api/webhook/telegram/{$channel->telegram_bot_token}";

        $this->info("Setting webhook to: {$webhookUrl}");

        try {
            $result = $telegramService->setWebhook($channel->telegram_bot_token, $webhookUrl);
            
            if ($result['ok'] ?? false) {
                $this->info('Webhook set successfully!');
                $this->line('Description: ' . ($result['description'] ?? 'Success'));
            } else {
                $this->error('Failed to set webhook.');
                $this->error('Response: ' . json_encode($result));
            }

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
