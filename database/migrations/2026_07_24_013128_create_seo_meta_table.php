<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable'); // seoable_type, seoable_id
            $table->string('locale', 12)->default('en');
            $table->string('title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('og_title')->nullable();
            $table->string('og_description', 320)->nullable();
            $table->string('og_image_path')->nullable();
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->json('schema_json_override')->nullable();
            $table->string('focus_keyword')->nullable();
            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id', 'locale'], 'seo_meta_entity_locale_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_meta');
    }
};
