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
        // 1. Add columns
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('bot_user_id')->nullable()->index()->after('channel_id')
                ->comment('ID do Bot (Phone ID ou Token) para persistência independente do canal');
        });

        Schema::table('feedback_entries', function (Blueprint $table) {
            $table->string('bot_user_id')->nullable()->index()->after('channel_id');
        });

        // 2. Migrate Data
        // Iterate Channels to update related conversations
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

        // 3. Relax channel_id constraints (Drop foreign key check to allow keeping history)
        // Schema::table('conversations', function (Blueprint $table) { ... })
        // Nota: Alterar foreign key é complexo dependendo do driver (SQLite vs MySQL).
        // Vamos apenas tornar a coluna nullable por segurança primeiro, se o driver permitir
        
        try {
            Schema::table('conversations', function (Blueprint $table) {
               $table->unsignedBigInteger('channel_id')->nullable()->change();
            });
            Schema::table('feedback_entries', function (Blueprint $table) {
               $table->unsignedBigInteger('channel_id')->nullable()->change();
            });
        } catch (\Exception $e) {
            // Se falhar (ex: SQLite limitation), seguimos sem isso por enquanto
            // Mas o ideal para persistência total seria remover o ON DELETE CASCADE
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn('bot_user_id');
        });
        
        Schema::table('feedback_entries', function (Blueprint $table) {
            $table->dropColumn('bot_user_id');
        });
    }
};
