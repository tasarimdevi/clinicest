<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('before_after_cases', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->foreignId('treatment_id')->constrained()->cascadeOnDelete();
            $table->json('title'); // translatable
            $table->json('description')->nullable(); // translatable
            // Nullable on purpose — see docs/04-wireframes.md §10: only
            // real, consent-verified photos are ever shown. A case with no
            // photos yet still renders (honestly, as "photos pending"),
            // never with a placeholder image standing in for a real result.
            $table->string('before_media_path')->nullable();
            $table->string('after_media_path')->nullable();
            $table->foreignId('patient_country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->boolean('consent_confirmed')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->index(['treatment_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('before_after_cases');
    }
};
