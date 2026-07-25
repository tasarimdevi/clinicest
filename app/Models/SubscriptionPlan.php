<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriptionTier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'tier', 'price_month', 'price_year',
        'currency', 'features_json', 'lead_quota', 'is_active', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'tier' => SubscriptionTier::class,
            'features_json' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }
}
