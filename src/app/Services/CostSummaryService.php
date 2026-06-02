<?php

namespace App\Services;

use App\Enums\MetricSource;
use App\Models\CostDailySummary;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CostSummaryService
{
    /** @return array<string, mixed> */
    public function monthlySummary(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $start = $now->startOfMonth()->toDateString();
        $today = $now->toDateString();

        $monthToDate = (float) CostDailySummary::query()
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $today)
            ->sum('amount');

        $todayIncrement = (float) CostDailySummary::query()
            ->whereDate('date', $today)
            ->sum('amount');

        $elapsedDays = max(1, $now->day);
        $daysInMonth = $now->daysInMonth;

        return [
            'currency' => config('meterpipe.default_currency', 'usd'),
            'month_to_date' => $monthToDate,
            'openai_month_to_date' => $this->sourceMonthToDate(MetricSource::OpenAi->value, $start, $today),
            'laravel_cloud_month_to_date' => $this->sourceMonthToDate(MetricSource::LaravelCloud->value, $start, $today),
            'today_increment' => $todayIncrement,
            'month_end_forecast' => $monthToDate / $elapsedDays * $daysInMonth,
            'provider_breakdown' => $this->providerBreakdown($start, $today),
            'app_breakdown' => $this->appBreakdown($start, $today),
        ];
    }

    private function sourceMonthToDate(string $source, string $start, string $today): float
    {
        return (float) CostDailySummary::query()
            ->where('source', $source)
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $today)
            ->sum('amount');
    }

    /** @return EloquentCollection<int, CostDailySummary> */
    private function providerBreakdown(string $start, string $today): EloquentCollection
    {
        return CostDailySummary::query()
            ->selectRaw('source, sum(amount) as total')
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $today)
            ->groupBy('source')
            ->orderByDesc('total')
            ->get();
    }

    /** @return EloquentCollection<int, CostDailySummary> */
    private function appBreakdown(string $start, string $today): EloquentCollection
    {
        return CostDailySummary::query()
            ->selectRaw('pipe_app_id, sum(amount) as total')
            ->whereNotNull('pipe_app_id')
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $today)
            ->groupBy('pipe_app_id')
            ->orderByDesc('total')
            ->with('pipeApp')
            ->get();
    }
}
