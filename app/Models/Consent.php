<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Consent extends Model
{
    protected $fillable = [
        'consentable_type', 'consentable_id', 'type', 'granted',
        'text_version', 'ip', 'user_agent', 'granted_at', 'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'granted' => 'boolean',
            'granted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function consentable(): MorphTo
    {
        return $this->morphTo();
    }
}
