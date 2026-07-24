<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LeadActivity extends Model
{
    public $timestamps = false;

    protected $fillable = ['lead_id', 'actor_type', 'actor_id', 'type', 'payload_json', 'created_at'];

    protected function casts(): array
    {
        return [
            'payload_json' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
}
