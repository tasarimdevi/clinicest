<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Matches docs/05-database-schema-erd.md's `subscription_plans` spec.
     * Reference data (the Verified/Growth/Elite tiers from docs/01 §3),
     * seeded by SubscriptionPlanSeeder. `tier` is App\Enums\SubscriptionTier
     * — deliberately distinct from VerificationTier despite the shared
     * "Elite" wording (a paid plan tier, not a trust badge). Prices are
     * integer minor units + currency, never floats. price_year is nullable
     * (a plan may be monthly-only). No soft deletes — is_active toggles a
     * plan off instead of deleting a row other subscriptions may point at.
     */
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tier'); // App\Enums\SubscriptionTier: verified|growth|elite
            $table->unsignedInteger('price_month'); // minor units
            $table->unsignedInteger('price_year')->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->json('features_json')->nullable();
            $table->unsignedInteger('lead_quota')->nullable(); // null = unlimited
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
