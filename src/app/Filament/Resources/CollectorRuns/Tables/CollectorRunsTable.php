<?php

namespace App\Filament\Resources\CollectorRuns\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CollectorRunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('started_at')->dateTime()->sortable(),
                TextColumn::make('collector_name')->searchable()->sortable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('fetched_count')->numeric()->sortable(),
                TextColumn::make('stored_count')->numeric()->sortable(),
                TextColumn::make('finished_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'running' => 'running',
                        'succeeded' => 'succeeded',
                        'failed' => 'failed',
                        'skipped' => 'skipped',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
