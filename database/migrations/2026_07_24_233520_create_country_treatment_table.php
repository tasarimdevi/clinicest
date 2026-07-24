<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reduced-scope stand-in for docs/05-database-schema-erd.md's
     * country_treatment pSEO table — flight_info_json/content_id/seo_id
     * are Phase 2+ (docs/10-roadmap.md) content-management concerns.
     * Both price pairs are stored in the SAME currency (the country's
     * own) so the /countries and /cost pages can show a same-currency
     * comparison without a live FX conversion at request time.
     */
    public function up(): void
    {
        Schema::create('country_treatment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->foreignId('treatment_id')->constrained()->cascadeOnDelete();
            $table->string('currency', 3);
            $table->unsignedInteger('local_price_min'); // typical price back home, minor units
            $table->unsignedInteger('local_price_max');
            $table->unsignedInteger('turkey_price_min'); // Clinicest/Turkey price, converted to local currency
            $table->unsignedInteger('turkey_price_max');
            $table->timestamps();

            $table->unique(['country_id', 'treatment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_treatment');
    }
};
