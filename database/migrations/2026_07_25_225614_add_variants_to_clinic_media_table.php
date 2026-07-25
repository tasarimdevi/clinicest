<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Realizes the docs/05-database-schema-erd.md media `variants_json`
     * idea (avif/webp/sizes) on the table that's actually in use.
     * `path` stays the canonical, downscaled+compressed original-format
     * image; `variants_json` holds derived paths ({"webp": "...",
     * "thumb": "..."}) and intrinsic dimensions ({"w":..,"h":..}) so the
     * public <picture> can serve WebP with a JPEG/PNG fallback and set
     * explicit width/height. Nullable: rows uploaded before this (or if
     * variant generation is ever skipped) simply fall back to `path`.
     */
    public function up(): void
    {
        Schema::table('clinic_media', function (Blueprint $table) {
            $table->json('variants_json')->nullable()->after('path');
            $table->unsignedSmallInteger('width')->nullable()->after('variants_json');
            $table->unsignedSmallInteger('height')->nullable()->after('width');
        });
    }

    public function down(): void
    {
        Schema::table('clinic_media', function (Blueprint $table) {
            $table->dropColumn(['variants_json', 'width', 'height']);
        });
    }
};
