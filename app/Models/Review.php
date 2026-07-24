<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * See docs/09-crm-admin-architecture.md §4 (moderation queue — not yet
 * built, docs/10-roadmap.md Phase 2; reviews are admin/seed-authored for
 * now, not patient-submitted, since post-treatment gating isn't wired).
 */
class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'public_id', 'reviewable_type', 'reviewable_id', 'user_id', 'reviewer_name',
        'reviewer_country_id', 'rating', 'title', 'body', 'treatment_id',
        'is_verified', 'status', 'ai_summary_cache', 'moderated_by', 'moderated_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $review) {
            $review->public_id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'moderated_at' => 'datetime',
        ];
    }

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewerCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'reviewer_country_id');
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    public function moderatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }
}
