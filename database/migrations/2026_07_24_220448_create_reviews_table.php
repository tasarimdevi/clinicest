<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->morphs('reviewable'); // clinic | doctor
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reviewer_name');
            $table->foreignId('reviewer_country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('body');
            $table->foreignId('treatment_id')->nullable()->constrained()->nullOnDelete();
            // Simplified stand-in for docs/05's treatment_case_id linkage —
            // full treatment-case tracking is Phase 3 CRM/billing scope.
            // Only reviews with is_verified=true render the "Verified"
            // badge; it's set by an admin/moderator, not self-reported.
            $table->boolean('is_verified')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('ai_summary_cache')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['reviewable_type', 'reviewable_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
