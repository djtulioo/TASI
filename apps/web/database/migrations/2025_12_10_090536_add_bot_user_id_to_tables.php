<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Conversations
        if (!Schema::hasColumn('conversations', 'bot_user_id')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->string('bot_user_id')->nullable()->index()->after('channel_id');
            });
        }

        // 2. Feedback Entries
        if (!Schema::hasColumn('feedback_entries', 'bot_user_id')) {
            Schema::table('feedback_entries', function (Blueprint $table) {
                $table->string('bot_user_id')->nullable()->index()->after('channel_id');
            });
        }

        // 3. Migrate Data
        DB::table('channels')->orderBy('id')->chunk(50, function ($channels) {
            foreach ($channels as $channel) {
                $botId = $channel->phone_number_id ?? $channel->telegram_bot_token;
                
                if ($botId) {
                    DB::table('conversations')
                        ->where('channel_id', $channel->id)
                        ->update(['bot_user_id' => $botId]);

                    DB::table('feedback_entries')
                        ->where('channel_id', $channel->id)
                        ->update(['bot_user_id' => $botId]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('conversations', 'bot_user_id')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->dropColumn('bot_user_id');
            });
        }
        
        if (Schema::hasColumn('feedback_entries', 'bot_user_id')) {
            Schema::table('feedback_entries', function (Blueprint $table) {
                $table->dropColumn('bot_user_id');
            });
        }
    }
};
