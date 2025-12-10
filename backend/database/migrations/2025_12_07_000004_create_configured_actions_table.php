<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configured_actions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Friendly name, e.g., Notify Admin via Slack');
            $table->enum('action_type', ['NOTIFY_EMAIL', 'NOTIFY_SLACK', 'DISABLE_ACCOUNT', 'DISABLE_TRADING', 'ALERT', 'CLOSE_TRADE']);
            $table->json('config')->nullable()->comment('Configuration like email address or webhook URL');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configured_actions');
    }
};
