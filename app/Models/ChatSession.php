<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id', 'lead_id', 'status', 'locale', 'page_context',
        'ip_hash', 'message_count', 'token_count', 'consent',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $session) {
            $session->public_id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'page_context' => 'array',
            'consent' => 'boolean',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }
}
