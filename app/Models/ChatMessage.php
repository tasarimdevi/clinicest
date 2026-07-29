<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'chat_session_id', 'role', 'content', 'original_draft',
        'tool_name', 'tool_input', 'tool_output', 'model',
        'latency_ms', 'flagged', 'flag_reason',
    ];

    protected function casts(): array
    {
        return [
            'tool_input' => 'array',
            'tool_output' => 'array',
            'flagged' => 'boolean',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }
}
