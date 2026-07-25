<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Translatable\HasTranslations;

class Faq extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['faqable_type', 'faqable_id', 'question', 'answer', 'category', 'sort', 'status'];

    public array $translatable = ['question', 'answer'];

    public function faqable(): MorphTo
    {
        return $this->morphTo();
    }
}
