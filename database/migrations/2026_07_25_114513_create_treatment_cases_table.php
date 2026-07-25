<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Matches docs/05-database-schema-erd.md's `treatment_cases` spec
     * field-for-field. Last link before Commission in the CRM chain
     * Lead -> Assignment -> Offer -> Appointment -> Treatment Case ->
     * Commission. No `offer_id` FK — the docs table spec doesn't have one
     * either; the admin UI lets a staff member pick an accepted offer to
     * prefill the form, but the case itself only records what was
     * actually agreed, not which offer produced it (same reasoning as
     * appointments having no offer_id).
     * `lead_id` is unique: the ERD marks this relation `LEADS ||--o|
     * TREATMENT_CASES` — a lead becomes at most one treatment case.
     */
    public function up(): void
    {
        Schema::create('treatment_cases', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('lead_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->json('treatment_ids_json')->nullable();
            $table->unsignedInteger('agreed_price'); // minor units
            $table->string('currency', 3)->default('EUR');
            $table->string('status')->default('planned'); // planned|in_treatment|completed|refunded
            $table->date('arrival_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_cases');
    }
};
