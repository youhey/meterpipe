<?php

namespace App\Services;

use App\Models\MetricSnapshot;

class UsageSummaryService
{
    public function metricCount(): int
    {
        return MetricSnapshot::query()->count();
    }
}
