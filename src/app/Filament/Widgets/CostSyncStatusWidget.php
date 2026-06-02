<?php

namespace App\Filament\Widgets;

use App\Models\CostProvider;
use Carbon\CarbonImmutable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class CostSyncStatusWidget extends TableWidget
{
    protected ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->latestRunsQuery())
            ->heading('Sync Status')
            ->columns([
                TextColumn::make('key')->label('provider')->badge(),
                TextColumn::make('latestSyncRun.status')
                    ->label('status')
                    ->badge(),
                TextColumn::make('sync_freshness')
                    ->label('freshness')
                    ->state(fn(CostProvider $record): string => $this->freshness($record))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'fresh' => 'success',
                        'stale' => 'warning',
                        'danger' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('last_synced_at')->dateTime(),
                TextColumn::make('latestSyncRun.records_fetched')->label('fetched')->numeric(),
                TextColumn::make('latestSyncRun.records_saved')->label('saved')->numeric(),
                TextColumn::make('latestSyncRun.error_message')->label('error')->limit(80),
            ]);
    }

    private function latestRunsQuery(): Builder
    {
        return CostProvider::query()
            ->with('latestSyncRun')
            ->orderBy('key');
    }

    private function freshness(CostProvider $provider): string
    {
        if ($provider->last_synced_at === null) {
            return 'never';
        }

        $hours = CarbonImmutable::parse((string) $provider->last_synced_at)->diffInHours(now());

        if ($hours >= 24) {
            return 'danger';
        }

        if ($hours >= 3) {
            return 'stale';
        }

        return 'fresh';
    }
}
