<?php

namespace App\Services;

use App\Enums\CollectorRunStatus;
use App\Models\CollectorRun;

class CollectorHealthService
{
    /** @return array<string, mixed> */
    public function summary(): array
    {
        $latestRun = CollectorRun::query()->latest('started_at')->first();
        $activeAlerts = CollectorRun::query()
            ->where('status', CollectorRunStatus::Failed->value)
            ->where('started_at', '>=', now()->subDay())
            ->count();

        return [
            'latest_run_at' => $latestRun?->started_at,
            'latest_run_status' => $latestRun?->getRawOriginal('status'),
            'active_alerts' => $activeAlerts,
        ];
    }
}
