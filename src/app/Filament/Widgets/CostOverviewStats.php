<?php

namespace App\Filament\Widgets;

use App\Services\CostSummaryService;
use Carbon\CarbonImmutable;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class CostOverviewStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    /** @var array<string, mixed>|null */
    private ?array $summary = null;

    public function getSectionContentComponent(): Component
    {
        return Section::make()
            ->heading('Cost')
            ->description($this->description())
            ->schema($this->getCachedStats())
            ->columns($this->getColumns())
            ->contained(false)
            ->gridContainer();
    }

    protected function getStats(): array
    {
        $summary = $this->summary();
        $currency = strtoupper((string) $summary['currency']);

        return [
            Stat::make('Total', $this->money($summary['month_to_date'], $currency)),
            Stat::make('OpenAI', $this->money($summary['openai_month_to_date'], $currency)),
            Stat::make('Laravel Cloud', $this->money($summary['laravel_cloud_month_to_date'], $currency)),
            Stat::make('Yesterday', $this->money($summary['yesterday_cost'], $currency)),
            Stat::make('Estimation', $this->money($summary['month_end_forecast'], $currency)),
        ];
    }

    /** @return array<string, mixed> */
    private function summary(): array
    {
        return $this->summary ??= app(CostSummaryService::class)->monthlySummary();
    }

    private function description(): HtmlString
    {
        $summary = $this->summary();
        $now = CarbonImmutable::now('UTC');
        $period = sprintf(
            '%d年%d月 (%s 〜 %s)',
            $now->year,
            $now->month,
            $now->startOfMonth()->format('Y/m/d'),
            $now->format('Y/m/d'),
        );

        $lastSyncedAt = $summary['last_synced_at'] ?? null;
        $syncDescription = $lastSyncedAt === null
            ? 'まだ同期データはありません。'
            : CarbonImmutable::parse((string) $lastSyncedAt)->setTimezone('UTC')->format('Y/m/d H:i:s') . ' UTC に最終同期したデータです。';
        $laravelCloudPeriod = $summary['laravel_cloud_billing_period'] ?? [];
        $laravelCloudDescription = null;

        if (is_array($laravelCloudPeriod) && is_string($laravelCloudPeriod['bucket_start'] ?? null) && is_string($laravelCloudPeriod['bucket_end'] ?? null)) {
            $laravelCloudDescription = sprintf(
                'Laravel Cloud は %s 〜 %s の請求期間データです。',
                CarbonImmutable::parse($laravelCloudPeriod['bucket_start'])->format('Y/m/d'),
                CarbonImmutable::parse($laravelCloudPeriod['bucket_end'])->format('Y/m/d'),
            );
        }

        return new HtmlString(implode('<br>', array_filter([
            e($period),
            e($syncDescription),
            $laravelCloudDescription === null ? null : e($laravelCloudDescription),
        ])));
    }

    private function money(mixed $amount, string $currency): string
    {
        return $currency . ' ' . number_format((float) $amount, 2);
    }
}
