<?php

namespace App\Filament\Resources\CostDailySummaries\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CostDailySummariesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('summary_date')->date()->sortable(),
                TextColumn::make('provider_key')->badge()->searchable()->sortable(),
                TextColumn::make('pipe_app_key')->label('App')->badge()->searchable()->sortable(),
                TextColumn::make('dimension_type')->badge()->searchable()->sortable(),
                TextColumn::make('dimension_label')->searchable()->sortable(),
                TextColumn::make('amount')->money('USD')->sortable(),
                TextColumn::make('currency')->badge()->sortable(),
                TextColumn::make('record_count')->numeric()->sortable(),
            ])
            ->filters([
                SelectFilter::make('provider_key')
                    ->options([
                        'openai' => 'openai',
                        'laravel_cloud' => 'laravel_cloud',
                        'all' => 'all',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
