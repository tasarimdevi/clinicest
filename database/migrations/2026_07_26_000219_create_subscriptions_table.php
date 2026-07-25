<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Matches docs/05-database-schema-erd.md's `subscriptions` spec. A
     * clinic's link to a plan. `provider`/`provider_ref` are the seam for
     * a real gateway (stripe|iyzico) and stay null in this deterministic
     * pass — a subscription is recorded/managed by staff, not driven by
     * gateway webhooks (see docs/10-roadmap.md; real billing is flagged,
     * not built). "At most one active subscription per clinic" is enforced
     * in AssignSubscription (old one canceled on switch), not as a DB
     * constraint, so the row history is preserved.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans');
            $table->string('status')->default('trialing'); // App\Enums\SubscriptionStatus
            $table->timestamp('started_at')->nullable();
            $table->timestamp('renews_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->string('provider')->nullable(); // stripe|iyzico — null while unautomated
            $table->string('provider_ref')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
