<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

/**
 * Backs both the Guide (kind=guide, one row with is_pillar=true plus its
 * cluster articles) and the Blog (kind=blog) — see the posts migration
 * docblock for the reduced-scope decisions.
 */
class Post extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'category_id', 'treatment_id', 'kind', 'is_pillar', 'slug',
        'title', 'excerpt', 'body', 'hero_image_path',
        'author_name', 'author_credential',
        'medical_reviewer_name', 'medical_reviewer_credential', 'reviewed_at',
        'meta_title', 'meta_description', 'status', 'published_at',
    ];

    public array $translatable = ['title', 'excerpt', 'body'];

    protected function casts(): array
    {
        return [
            'is_pillar' => 'boolean',
            'reviewed_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(Treatment::class);
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable');
    }

    /**
     * Word-count estimate rather than a stored column, so it can't drift
     * from the actual body content.
     */
    public function readingMinutes(): int
    {
        $words = str_word_count(strip_tags($this->getTranslation('body', 'en') ?? ''));

        return max(1, (int) ceil($words / 200));
    }
}
