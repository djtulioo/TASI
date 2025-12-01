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
        Schema::table('channels', function (Blueprint $table) {
            $table->string('type')->default('whatsapp')->after('name');
            $table->string('telegram_bot_token')->nullable()->after('type');
            
            // Make WhatsApp specific fields nullable
            $table->string('official_whatsapp_number')->nullable()->change();
            $table->string('app_id')->nullable()->change();
            $table->string('app_secret')->nullable()->change();
            $table->text('access_token')->nullable()->change();
            $table->string('phone_number_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn(['type', 'telegram_bot_token']);
            
            // Revert nullable changes (this might fail if there are null values, but for rollback it's expected)
            $table->string('official_whatsapp_number')->nullable(false)->change();
            $table->string('app_id')->nullable(false)->change();
            $table->string('app_secret')->nullable(false)->change();
            $table->text('access_token')->nullable(false)->change();
            $table->string('phone_number_id')->nullable(false)->change();
        });
    }
};
