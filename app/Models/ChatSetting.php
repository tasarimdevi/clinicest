<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Single-row config table for the AI Chat Assistant kill switch and abuse
 * caps — editable from the admin panel without a redeploy. Not a generic
 * key/value store (none exists in this codebase); one feature, one row.
 */
class ChatSetting extends Model
{
    protected $fillable = [
        'enabled', 'daily_budget_tokens', 'tokens_used_today', 'budget_date',
        'max_messages_per_session', 'max_sessions_per_ip_per_hour',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'budget_date' => 'date',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], ['enabled' => false]);
    }

    public function hasBudgetRemaining(): bool
    {
        if ($this->budget_date === null || ! $this->budget_date->isToday()) {
            $this->forceFill(['tokens_used_today' => 0, 'budget_date' => now()->toDateString()])->save();
        }

        return $this->tokens_used_today < $this->daily_budget_tokens;
    }

    public function recordTokensUsed(int $tokens): void
    {
        $this->increment('tokens_used_today', $tokens);
    }
}
