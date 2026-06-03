<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CostOverviewStats;
use App\Filament\Widgets\CostSyncStatusWidget;
use App\Filament\Widgets\LaravelCloudCostByApplicationChart;
use App\Filament\Widgets\LaravelCloudCostByResourceTypeChart;
use App\Filament\Widgets\OpenAiCostByLineItemChart;
use App\Filament\Widgets\OpenAiCostByProjectChart;
use App\Filament\Widgets\ProviderCostTrendChart;
use App\Filament\Widgets\TotalCostTrendChart;
use App\Services\Costs\CostSyncPeriod;
use App\Services\Costs\CostSyncService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class CostDashboard extends Page
{
    protected string $view = 'filament.pages.cost-dashboard';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $navigationLabel = 'Cost Dashboard';

    protected static ?string $title = 'Cost Dashboard';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncAllCosts')
                ->label('Sync all costs')
                ->action(fn() => $this->queueSync('all')),
            Action::make('syncOpenAiCosts')
                ->label('Sync OpenAI costs')
                ->action(fn() => $this->queueSync('openai')),
            Action::make('syncLaravelCloudCosts')
                ->label('Sync Laravel Cloud costs')
                ->action(fn() => $this->queueSync('laravel_cloud')),
            Action::make('recalculateSummaries')
                ->label('Recalculate summaries')
                ->action(function (): void {
                    [$from, $to] = app(CostSyncPeriod::class)->resolve(null, null, 30);
                    app(\App\Services\Costs\CostSummaryRecalculator::class)->recalculate($from, $to);
                    Notification::make()->title('コスト集計を再計算しました')->success()->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CostSyncStatusWidget::class,
            CostOverviewStats::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            TotalCostTrendChart::class,
            ProviderCostTrendChart::class,
            LaravelCloudCostByApplicationChart::class,
            LaravelCloudCostByResourceTypeChart::class,
            OpenAiCostByProjectChart::class,
            OpenAiCostByLineItemChart::class,
        ];
    }

    private function queueSync(string $provider): void
    {
        [$from, $to] = app(CostSyncPeriod::class)->resolve(null, null, 30);
        $service = app(CostSyncService::class);

        if ($provider === 'all' || $provider === 'openai') {
            $service->queueOpenAi($from, $to);
        }

        if ($provider === 'all' || $provider === 'laravel_cloud') {
            $service->queueLaravelCloud($from, $to);
        }

        Notification::make()
            ->title('同期をキューに追加しました')
            ->success()
            ->send();
    }
}
