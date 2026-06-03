<?php

namespace App\Filament\Resources\CostSyncRuns\Tables;

use App\Enums\CostProviderKey;
use App\Models\CostSyncRun;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CostSyncRunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider_key')->badge()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('scope')->badge()->sortable(),
                TextColumn::make('period_start')->dateTime()->sortable(),
                TextColumn::make('period_end')->dateTime()->sortable(),
                TextColumn::make('records_fetched')->numeric()->sortable(),
                TextColumn::make('records_saved')->numeric()->sortable(),
                TextColumn::make('finished_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('provider_key')
                    ->options([
                        CostProviderKey::OpenAi->value => 'openai',
                        CostProviderKey::LaravelCloud->value => 'laravel_cloud',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        CostSyncRun::QUEUED => 'queued',
                        CostSyncRun::RUNNING => 'running',
                        CostSyncRun::SUCCEEDED => 'succeeded',
                        CostSyncRun::FAILED => 'failed',
                        CostSyncRun::SKIPPED => 'skipped',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
