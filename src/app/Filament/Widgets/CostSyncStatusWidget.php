<?php

namespace App\Filament\Widgets;

use App\Enums\CostProviderKey;
use App\Models\CostSyncRun;
use Carbon\CarbonImmutable;
use Filament\Widgets\Widget;

class CostSyncStatusWidget extends Widget
{
    protected string $view = 'filament.widgets.cost-sync-status-widget';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    protected function getPollingInterval(): ?string
    {
        return $this->pollingInterval;
    }

    /** @return list<array<string, mixed>> */
    public function getRows(): array
    {
        $latestRuns = CostSyncRun::query()
            ->whereIn('id', CostSyncRun::query()
                ->selectRaw('max(id)')
                ->groupBy('provider_key'))
            ->get()
            ->keyBy('provider_key');

        $latestSuccessfulRuns = CostSyncRun::query()
            ->where('status', CostSyncRun::SUCCEEDED)
            ->whereIn('id', CostSyncRun::query()
                ->selectRaw('max(id)')
                ->where('status', CostSyncRun::SUCCEEDED)
                ->groupBy('provider_key'))
            ->get()
            ->keyBy('provider_key');

        return array_map(function (CostProviderKey $provider) use ($latestRuns, $latestSuccessfulRuns): array {
            $latestRun = $latestRuns->get($provider->value);
            $latestSuccessfulRun = $latestSuccessfulRuns->get($provider->value);
            $lastSyncedAt = $latestSuccessfulRun instanceof CostSyncRun ? $latestSuccessfulRun->finished_at : null;
            $freshness = $this->freshness($lastSyncedAt);

            return [
                'provider' => $provider->value,
                'provider_label' => $provider->label(),
                'enabled' => (bool) config($provider->enabledConfigPath(), false),
                'status' => $latestRun instanceof CostSyncRun ? $latestRun->status : 'never',
                'freshness' => $freshness,
                'freshness_color' => $this->freshnessColor($freshness),
                'last_synced_at' => $lastSyncedAt,
                'records_fetched' => $latestRun instanceof CostSyncRun ? $latestRun->records_fetched : 0,
                'records_saved' => $latestRun instanceof CostSyncRun ? $latestRun->records_saved : 0,
                'error_message' => $latestRun instanceof CostSyncRun ? $latestRun->error_message : null,
            ];
        }, CostProviderKey::syncable());
    }

    private function freshness(mixed $lastSyncedAt): string
    {
        if ($lastSyncedAt === null) {
            return 'never';
        }

        $hours = CarbonImmutable::parse((string) $lastSyncedAt)->diffInHours(now());

        if ($hours >= 24) {
            return 'danger';
        }

        if ($hours >= 3) {
            return 'stale';
        }

        return 'fresh';
    }

    private function freshnessColor(string $freshness): string
    {
        return match ($freshness) {
            'fresh' => 'success',
            'stale' => 'warning',
            'danger' => 'danger',
            default => 'gray',
        };
    }
}
