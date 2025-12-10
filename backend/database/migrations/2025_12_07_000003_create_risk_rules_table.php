<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Friendly name for the rule');
            $table->string('rule_type')->comment('Class name that implements the rule logic, e.g., MinDurationRule');
            $table->json('parameters')->nullable()->comment('Dynamic JSON parameters for the rule');
            $table->enum('severity', ['HARD', 'SOFT'])->default('SOFT');
            $table->integer('incident_limit')->default(1)->comment('For SOFT rules: number of incidents before action is triggered');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_rules');
    }
};
