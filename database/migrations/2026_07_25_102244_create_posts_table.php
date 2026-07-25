<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reduced-scope content model backing both the Guide pillar/cluster
     * pages and the Blog (docs/04-wireframes.md §11-12) — one table for
     * both since they're the same "article" shape, distinguished by
     * `kind`. Cut from this pass:
     * - No file-upload widget exists anywhere else in the admin panel
     *   yet, so `hero_image_path` is a plain nullable text field (a URL
     *   an editor pastes in), not a real media upload.
     * - `body` is trusted-author HTML (rendered raw, not sanitized) —
     *   there's no WYSIWYG/Markdown pipeline in this project; editors
     *   with content.edit write HTML directly, same trust level as any
     *   other admin-only input.
     * - No `reading_minutes` column — computed from body length instead
     *   of stored, so it can't drift from the actual content.
     * - No PDF export, no newsletter capture (no email-marketing system
     *   exists yet) — both mentioned in the wireframe, both Phase 4+.
     * - `medical_reviewer_*` fields are honestly nullable: demo/seed data
     *   never fabricates a reviewer, matching the project's no-fake-
     *   reviews rule extended to editorial content.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('post_categories')->nullOnDelete();
            $table->foreignId('treatment_id')->nullable()->constrained('treatments')->nullOnDelete();
            $table->string('kind'); // guide|blog
            $table->boolean('is_pillar')->default(false); // guide only: the single pillar page
            $table->string('slug')->unique();
            $table->json('title'); // translatable
            $table->json('excerpt')->nullable(); // translatable
            $table->json('body'); // translatable, trusted-author HTML
            $table->string('hero_image_path')->nullable();
            $table->string('author_name')->nullable();
            $table->string('author_credential')->nullable();
            $table->string('medical_reviewer_name')->nullable();
            $table->string('medical_reviewer_credential')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('status')->default('draft'); // draft|published
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kind', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
