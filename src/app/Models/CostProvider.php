<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['key', 'name', 'is_enabled', 'settings', 'last_synced_at'])]
class CostProvider extends Model
{
    public const OPENAI = 'openai';

    public const LARAVEL_CLOUD = 'laravel_cloud';

    public const ALL = 'all';

    /** @return HasOne<CostSyncRun, $this> */
    public function latestSyncRun(): HasOne
    {
        return $this->hasOne(CostSyncRun::class, 'provider_key', 'key')->latestOfMany();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'settings' => 'array',
            'last_synced_at' => 'immutable_datetime',
        ];
    }
}
