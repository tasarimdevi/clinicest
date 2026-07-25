<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Retrofits the docs/05-database-schema-erd.md `treatment_case_id`
     * linkage this table's own original docblock deferred ("Simplified
     * stand-in... is_verified=true... set by an admin/moderator, not
     * self-reported"). Now that the patient portal lets a patient submit
     * their own review, `lead_id` is what makes "verified" actually mean
     * something again: is_verified is set automatically from whether
     * that lead has a completed treatment_case with the clinic being
     * reviewed, not just an admin's say-so. Nullable because existing/
     * future admin-seeded reviews (no real patient behind them) stay
     * legitimately unlinked — a null lead_id review can still be
     * hand-verified by a moderator, same as before.
     * Unique per (lead_id, reviewable_type, reviewable_id) so a patient
     * can't submit more than one review of the same clinic/doctor from
     * their portal link — doesn't apply to null lead_id rows (each NULL
     * is distinct under a unique index).
     */
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('lead_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->unique(['lead_id', 'reviewable_type', 'reviewable_id']);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['lead_id', 'reviewable_type', 'reviewable_id']);
            $table->dropConstrainedForeignId('lead_id');
        });
    }
};
