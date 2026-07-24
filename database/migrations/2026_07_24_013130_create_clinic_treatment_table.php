<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_treatment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('treatment_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('price_min')->nullable(); // minor units
            $table->unsignedInteger('price_max')->nullable(); // minor units
            $table->string('currency', 3)->default('EUR');
            $table->text('notes')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['clinic_id', 'treatment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_treatment');
    }
};
