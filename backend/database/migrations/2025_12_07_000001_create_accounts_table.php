<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('login')->unique()->comment('Trading account number, e.g., 21002025');
            $table->enum('status', ['enable', 'disable'])->default('enable')->comment('General account status');
            $table->enum('trading_status', ['enable', 'disable'])->default('enable')->comment('Whether trading is allowed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
