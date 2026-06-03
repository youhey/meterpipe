<?php

namespace App\Services;

use App\Enums\CostProviderKey;
use App\Enums\IntegrationProvider;
use App\Models\AppIntegration;
use App\Models\CostDailySummary;
use App\Models\CostRecord;
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
        $monthStart = $now->startOfMonth();
        $monthEnd = $now->endOfMonth();
        $start = $monthStart->toDateString();
        $today = $now->toDateString();
        $yesterday = $now->subDay()->toDateString();
        $openAiMonthToDate = $this->providerMonthToDate(CostProviderKey::OpenAi->value, $start, $today);
        $laravelCloudBillingPeriod = $this->laravelCloudBillingPeriodTotal($monthStart, $monthEnd);
        $monthToDate = $openAiMonthToDate + $laravelCloudBillingPeriod['amount'];

        $openAiForecast = $openAiMonthToDate / max(1, $now->day) * $now->daysInMonth;

        $yesterdayCost = (float) CostDailySummary::query()
            ->where('provider_key', CostProviderKey::All->value)
            ->whereNull('dimension_type')
            ->whereDate('summary_date', $yesterday)
            ->sum('amount');

        return [
            'currency' => config('meterpipe.default_currency', 'usd'),
            'month_to_date' => $monthToDate,
            'openai_month_to_date' => $openAiMonthToDate,
            'laravel_cloud_month_to_date' => $laravelCloudBillingPeriod['amount'],
            'laravel_cloud_billing_period' => $laravelCloudBillingPeriod,
            'yesterday_cost' => $yesterdayCost,
            'month_end_forecast' => $openAiForecast + $laravelCloudBillingPeriod['amount'],
            'provider_breakdown' => $this->providerBreakdown($start, $today, $laravelCloudBillingPeriod['amount']),
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

        if ($providerKey === CostProviderKey::LaravelCloud->value) {
            return $this->laravelCloudDimensionBreakdown($dimensionType, $now);
        }

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

    /** @return Collection<int, \stdClass> */
    private function providerBreakdown(string $start, string $today, float $laravelCloudAmount): Collection
    {
        $rows = DB::table('cost_daily_summaries')
            ->selectRaw('provider_key, sum(amount) as total')
            ->whereIn('provider_key', [CostProviderKey::OpenAi->value])
            ->whereNull('dimension_type')
            ->whereDate('summary_date', '>=', $start)
            ->whereDate('summary_date', '<=', $today)
            ->groupBy('provider_key')
            ->orderByDesc('total')
            ->get();

        if ($laravelCloudAmount > 0.0) {
            $row = new \stdClass();
            $row->provider_key = CostProviderKey::LaravelCloud->value;
            $row->total = $laravelCloudAmount;
            $rows->push($row);
        }

        return $rows;
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

    /** @return array{amount: float, bucket_start: ?string, bucket_end: ?string} */
    private function laravelCloudBillingPeriodTotal(CarbonImmutable $monthStart, CarbonImmutable $monthEnd): array
    {
        $record = CostRecord::query()
            ->where('provider_key', CostProviderKey::LaravelCloud->value)
            ->where('source_dimension_type', 'total')
            ->whereDate('bucket_start', '<=', $monthEnd->toDateString())
            ->whereDate('bucket_end', '>=', $monthStart->toDateString())
            ->orderByDesc('bucket_start')
            ->orderByDesc('synced_at')
            ->first();

        if (! $record instanceof CostRecord) {
            return [
                'amount' => 0.0,
                'bucket_start' => null,
                'bucket_end' => null,
            ];
        }

        return [
            'amount' => (float) $record->amount,
            'bucket_start' => $this->dateString($record->bucket_start),
            'bucket_end' => $this->dateString($record->bucket_end),
        ];
    }

    /** @return array{labels: list<string>, values: list<float>} */
    private function laravelCloudDimensionBreakdown(string $dimensionType, CarbonImmutable $now): array
    {
        $field = match ($dimensionType) {
            'application' => 'external_application_id',
            'resource_type' => 'resource_type',
            'line_item' => 'line_item',
            default => null,
        };

        if ($field === null) {
            return ['labels' => [], 'values' => []];
        }

        $query = DB::table('cost_records')
            ->selectRaw($field . ' as dimension_key, sum(amount) as total')
            ->where('provider_key', CostProviderKey::LaravelCloud->value)
            ->whereNotNull($field)
            ->whereDate('bucket_start', '<=', $now->endOfMonth()->toDateString())
            ->whereDate('bucket_end', '>=', $now->startOfMonth()->toDateString())
            ->groupBy($field)
            ->orderByDesc('total');

        $rows = $dimensionType === 'application'
            ? $query->get()
            : $query->limit(12)->get();

        if ($dimensionType === 'application') {
            return $this->laravelCloudApplicationBreakdown($rows);
        }

        return [
            'labels' => $rows->map(fn(object $record): string => (string) ($record->dimension_key ?? 'Unmapped'))->values()->all(),
            'values' => $rows->map(fn(object $record): float => (float) $record->total)->values()->all(),
        ];
    }

    /**
     * @param Collection<int, \stdClass> $rows
     *
     * @return array{labels: list<string>, values: list<float>}
     */
    private function laravelCloudApplicationBreakdown(Collection $rows): array
    {
        $applicationIds = $rows
            ->map(fn(object $record): ?string => $record->dimension_key !== null ? (string) $record->dimension_key : null)
            ->filter()
            ->values()
            ->all();

        $labels = AppIntegration::query()
            ->with('pipeApp:id,key,name')
            ->where('provider', IntegrationProvider::LaravelCloud->value)
            ->where('enabled', true)
            ->whereNotNull('provider_resource_id')
            ->whereIn('provider_resource_id', $applicationIds)
            ->get()
            ->mapWithKeys(fn(AppIntegration $integration): array => [
                (string) $integration->provider_resource_id => (string) ($integration->pipeApp->name ?? $integration->pipeApp->key ?? $integration->provider_resource_id),
            ]);

        $totals = [];

        foreach ($rows as $record) {
            $dimensionKey = $record->dimension_key !== null ? (string) $record->dimension_key : 'Unmapped';
            $label = (string) ($labels->get($dimensionKey) ?? $dimensionKey);

            $totals[$label] = ($totals[$label] ?? 0.0) + (float) $record->total;
        }

        arsort($totals);

        $totals = array_slice($totals, 0, 12, true);

        return [
            'labels' => array_keys($totals),
            'values' => array_values($totals),
        ];
    }
}
