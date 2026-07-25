<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Self-onboarding (docs/09-crm-admin-architecture.md §3: "apply ->
     * upload license/credentials/ISO docs -> admin verification workflow ->
     * profile build -> choose subscription -> go live"). Reduced scope:
     * - `credentials_url` is a pasted link, not a real upload — no file
     *   upload subsystem exists anywhere else in this admin either (see
     *   the posts migration's hero_image_path for the same call).
     * - No subscription step — needs a payment provider (Stripe/iyzico),
     *   same cut already made for commissions/invoicing.
     * - `verification_tier`/`is_active` already existed and are reused as
     *   the actual go-live gate; `application_status` only tracks the
     *   *review* step itself (pending/approved/rejected), since neither
     *   existing field can distinguish "awaiting review" from "an admin
     *   just hasn't activated this yet". Defaults to 'approved' so every
     *   clinic created directly by an admin (the existing ClinicForm flow)
     *   is unaffected — only self-submitted applications start 'pending'.
     */
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->string('application_status')->default('approved')->after('owner_user_id'); // pending|approved|rejected
            $table->text('application_message')->nullable()->after('application_status');
            $table->string('credentials_url')->nullable()->after('application_message');
            $table->text('rejection_reason')->nullable()->after('credentials_url');
            $table->timestamp('applied_at')->nullable()->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn(['application_status', 'application_message', 'credentials_url', 'rejection_reason', 'applied_at']);
        });
    }
};
