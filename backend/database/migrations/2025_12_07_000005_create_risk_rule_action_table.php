<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_rule_action', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_rule_id')->constrained()->onDelete('cascade');
            $table->foreignId('configured_action_id')->constrained()->onDelete('cascade');
            $table->unique(['risk_rule_id', 'configured_action_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_rule_action');
    }
};
