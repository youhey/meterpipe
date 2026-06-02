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
                TextColumn::make('date')->date()->sortable(),
                TextColumn::make('source')->badge()->searchable()->sortable(),
                TextColumn::make('pipeApp.key')->label('App')->searchable()->sortable(),
                TextColumn::make('service')->searchable()->sortable(),
                TextColumn::make('amount')->money('USD')->sortable(),
                TextColumn::make('currency')->badge()->sortable(),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->options([
                        'openai' => 'openai',
                        'laravel_cloud' => 'laravel_cloud',
                        'manual' => 'manual',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
