<?php

namespace App\Models;

use App\Enums\PipeAppStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'name', 'description', 'repository_url', 'base_url', 'status', 'metadata'])]
class PipeApp extends Model
{
    /** @return HasMany<AppIntegration, $this> */
    public function integrations(): HasMany
    {
        return $this->hasMany(AppIntegration::class);
    }

    /** @return HasMany<MetricSnapshot, $this> */
    public function metricSnapshots(): HasMany
    {
        return $this->hasMany(MetricSnapshot::class);
    }

    /** @return HasMany<CostDailySummary, $this> */
    public function costDailySummaries(): HasMany
    {
        return $this->hasMany(CostDailySummary::class);
    }

    /** @return HasMany<AnalyticsEvent, $this> */
    public function analyticsEvents(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'status' => PipeAppStatus::class,
        ];
    }
}
