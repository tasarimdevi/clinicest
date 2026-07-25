<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Matches docs/05-database-schema-erd.md's `appointments` spec exactly
     * (Lead -> Assignment -> Offer -> Appointment -> Treatment Case ->
     * Commission chain, docs/09-crm-admin-architecture.md §2). No offer_id —
     * an appointment can be a pre-offer remote consult, not only a
     * post-acceptance booking, so it isn't FK'd to a specific offer.
     * No new LeadStatus case added for "appointment scheduled": the
     * existing enum (New..Won/Lost/Invalid) has no slot for it and adding
     * one is a bigger change than this pass's scope — see the
     * RequestAppointment action docblock.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('type'); // App\Enums\AppointmentType: remote_consult|onsite
            $table->dateTime('scheduled_at');
            $table->string('timezone', 64);
            $table->string('status')->default('requested'); // App\Enums\AppointmentStatus
            $table->string('meeting_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['lead_id', 'clinic_id']);
            $table->index(['clinic_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
