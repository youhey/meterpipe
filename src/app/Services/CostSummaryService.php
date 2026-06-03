<?php

namespace App\Services;

use App\Enums\CostProviderKey;
use App\Models\CostDailySummary;
use App\Models\CostSyncRun;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CostSummaryService
{
    /** @return array<string, mixed> */
    public function monthlySummary(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $start = $now->startOfMonth()->toDateString();
        $today = $now->toDateString();
        $yesterday = $now->subDay()->toDateString();

        $monthToDate = (float) CostDailySummary::query()
            ->where('provider_key', CostProviderKey::All->value)
            ->whereNull('dimension_type')
            ->whereDate('summary_date', '>=', $start)
            ->whereDate('summary_date', '<=', $today)
            ->sum('amount');

        $yesterdayCost = (float) CostDailySummary::query()
            ->where('provider_key', CostProviderKey::All->value)
            ->whereNull('dimension_type')
            ->whereDate('summary_date', $yesterday)
            ->sum('amount');

        $elapsedDays = max(1, $now->day);
        $daysInMonth = $now->daysInMonth;

        return [
            'currency' => config('meterpipe.default_currency', 'usd'),
            'month_to_date' => $monthToDate,
            'openai_month_to_date' => $this->providerMonthToDate(CostProviderKey::OpenAi->value, $start, $today),
            'laravel_cloud_month_to_date' => $this->providerMonthToDate(CostProviderKey::LaravelCloud->value, $start, $today),
            'yesterday_cost' => $yesterdayCost,
            'month_end_forecast' => $monthToDate / $elapsedDays * $daysInMonth,
            'provider_breakdown' => $this->providerBreakdown($start, $today),
            'app_breakdown' => $this->appBreakdown($start, $today),
            'last_synced_at' => CostSyncRun::query()
                ->where('status', CostSyncRun::SUCCEEDED)
                ->max('finished_at'),
        ];
    }

    /** @return array{labels: list<string>, values: list<float>} */
    public function totalTrend(int $days = 30): array
    {
        $now = CarbonImmutable::now();
        $start = $now->subDays($days - 1)->startOfDay();
        $totals = CostDailySummary::query()
            ->where('provider_key', CostProviderKey::All->value)
            ->whereNull('dimension_type')
            ->whereDate('summary_date', '>=', $start->toDateString())
            ->orderBy('summary_date')
            ->get()
            ->mapWithKeys(fn(CostDailySummary $summary): array => [$this->dateString($summary->summary_date) => (float) $summary->amount]);

        return $this->series($start, $days, fn(string $date): float => (float) ($totals->get($date, 0)));
    }

    /** @return array{labels: list<string>, openai: list<float>, laravel_cloud: list<float>} */
    public function providerTrend(int $days = 30): array
    {
        $now = CarbonImmutable::now();
        $start = $now->subDays($days - 1)->startOfDay();
        $rows = CostDailySummary::query()
            ->whereIn('provider_key', [CostProviderKey::OpenAi->value, CostProviderKey::LaravelCloud->value])
            ->whereNull('dimension_type')
            ->whereDate('summary_date', '>=', $start->toDateString())
            ->get()
            ->mapWithKeys(fn(CostDailySummary $summary): array => [
                $summary->provider_key . ':' . $this->dateString($summary->summary_date) => (float) $summary->amount,
            ]);

        $series = $this->series($start, $days, fn(): float => 0);

        return [
            'labels' => $series['labels'],
            'openai' => array_map(fn(string $date): float => (float) $rows->get(CostProviderKey::OpenAi->value . ':' . $date, 0), $this->dateLabels($start, $days)),
            'laravel_cloud' => array_map(fn(string $date): float => (float) $rows->get(CostProviderKey::LaravelCloud->value . ':' . $date, 0), $this->dateLabels($start, $days)),
        ];
    }

    /** @return array{labels: list<string>, values: list<float>} */
    public function dimensionBreakdown(string $providerKey, string $dimensionType, ?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $rows = DB::table('cost_daily_summaries')
            ->selectRaw('dimension_key, dimension_label, sum(amount) as total')
            ->where('provider_key', $providerKey)
            ->where('dimension_type', $dimensionType)
            ->whereDate('summary_date', '>=', $now->startOfMonth()->toDateString())
            ->whereDate('summary_date', '<=', $now->toDateString())
            ->groupBy('dimension_key', 'dimension_label')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        return [
            'labels' => $rows->map(fn(object $summary): string => (string) ($summary->dimension_label ?? $summary->dimension_key ?? 'Unmapped'))->values()->all(),
            'values' => $rows->map(fn(object $summary): float => (float) $summary->total)->values()->all(),
        ];
    }

    /** @return Collection<int, CostSyncRun> */
    public function syncStatuses(): Collection
    {
        return CostSyncRun::query()
            ->whereIn('id', CostSyncRun::query()
                ->selectRaw('max(id)')
                ->groupBy('provider_key'))
            ->orderBy('provider_key')
            ->get();
    }

    public function hasCostData(): bool
    {
        return CostDailySummary::query()->exists();
    }

    private function providerMonthToDate(string $providerKey, string $start, string $today): float
    {
        return (float) CostDailySummary::query()
            ->where('provider_key', $providerKey)
            ->whereNull('dimension_type')
            ->whereDate('summary_date', '>=', $start)
            ->whereDate('summary_date', '<=', $today)
            ->sum('amount');
    }

    /** @return Collection<int, CostDailySummary> */
    private function providerBreakdown(string $start, string $today): Collection
    {
        return CostDailySummary::query()
            ->selectRaw('provider_key, sum(amount) as total')
            ->whereIn('provider_key', [CostProviderKey::OpenAi->value, CostProviderKey::LaravelCloud->value])
            ->whereNull('dimension_type')
            ->whereDate('summary_date', '>=', $start)
            ->whereDate('summary_date', '<=', $today)
            ->groupBy('provider_key')
            ->orderByDesc('total')
            ->get();
    }

    /** @return Collection<int, CostDailySummary> */
    private function appBreakdown(string $start, string $today): Collection
    {
        return CostDailySummary::query()
            ->selectRaw('pipe_app_key, sum(amount) as total')
            ->whereNotNull('pipe_app_key')
            ->whereDate('summary_date', '>=', $start)
            ->whereDate('summary_date', '<=', $today)
            ->groupBy('pipe_app_key')
            ->orderByDesc('total')
            ->get();
    }

    /** @return array{labels: list<string>, values: list<float>} */
    private function series(CarbonImmutable $start, int $days, callable $valueResolver): array
    {
        $labels = $this->dateLabels($start, $days);

        return [
            'labels' => $labels,
            'values' => array_map($valueResolver, $labels),
        ];
    }

    /** @return list<string> */
    private function dateLabels(CarbonImmutable $start, int $days): array
    {
        $labels = [];

        for ($index = 0; $index < $days; $index++) {
            $labels[] = $start->addDays($index)->toDateString();
        }

        return $labels;
    }

    private function dateString(mixed $value): string
    {
        return CarbonImmutable::parse((string) $value)->toDateString();
    }
}
