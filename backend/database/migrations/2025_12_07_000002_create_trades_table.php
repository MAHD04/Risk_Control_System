<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['BUY', 'SELL']);
            $table->decimal('volume', 10, 2)->comment('Lot size');
            $table->timestamp('open_time');
            $table->timestamp('close_time')->nullable();
            $table->decimal('open_price', 10, 5);
            $table->decimal('close_price', 10, 5)->nullable();
            $table->enum('status', ['OPEN', 'CLOSED'])->default('OPEN');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
