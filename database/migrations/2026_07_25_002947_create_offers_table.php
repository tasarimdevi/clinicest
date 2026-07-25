<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reduced-scope version of docs/05-database-schema-erd.md's `offers`
     * table (Lead -> Assignment -> Offer -> Appointment -> Treatment Case ->
     * Commission chain, docs/09-crm-admin-architecture.md §2). Cut from
     * this pass:
     * - Explicit version-chain tracking. docs/09 §3 calls offers
     *   "versioned" but docs/05's own table spec has no version column —
     *   a revised offer is simply a new row (ordered by created_at);
     *   history is the list of rows, not a version FK.
     * - `created_by`: the acting user is already captured on the matching
     *   `lead_activities` row (type=system, event=offer_sent), same
     *   pattern as lead_assignments' status changes.
     * - Patient-portal viewed/accepted tracking is manual for now (no
     *   patient portal exists yet) — status is updated by an admin from
     *   the Lead detail page as a stand-in, same as lead status today.
     */
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('title');
            $table->text('treatment_plan')->nullable();
            $table->unsignedInteger('price_total'); // minor units, sum of breakdown_json line items
            $table->string('currency', 3)->default('EUR');
            $table->json('breakdown_json')->nullable(); // [{treatment_id, label, price}]
            $table->json('includes_json')->nullable(); // {hotel, transfer, warranty_years}
            $table->date('valid_until')->nullable();
            $table->string('status')->default('sent'); // App\Enums\OfferStatus
            $table->timestamps();

            $table->index(['lead_id', 'clinic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
