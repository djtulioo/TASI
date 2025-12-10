<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Conversations
        Schema::table('conversations', function (Blueprint $table) {
            // Drop foreign key
            // Note: Laravel automatically guesses the index name 'conversations_channel_id_foreign'
            $table->dropForeign(['channel_id']);
            
            // Make column nullable and re-add foreign key with SET NULL
            $table->foreignId('channel_id')->nullable()->change();
            $table->foreign('channel_id')
                  ->references('id')
                  ->on('channels')
                  ->nullOnDelete();
        });

        // 2. Feedback Entries
        Schema::table('feedback_entries', function (Blueprint $table) {
             // Assuming default index name 'feedback_entries_channel_id_foreign'
            $table->dropForeign(['channel_id']);
            
            $table->foreignId('channel_id')->nullable()->change();
            $table->foreign('channel_id')
                  ->references('id')
                  ->on('channels')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to CASCADE
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['channel_id']);
            $table->foreignId('channel_id')->nullable(false)->change();
            $table->foreign('channel_id')
                  ->references('id')
                  ->on('channels')
                  ->onDelete('cascade');
        });

        Schema::table('feedback_entries', function (Blueprint $table) {
            $table->dropForeign(['channel_id']);
            $table->foreignId('channel_id')->nullable(false)->change();
            $table->foreign('channel_id')
                  ->references('id')
                  ->on('channels')
                  ->onDelete('cascade');
        });
    }
};
