<?php

namespace App\Filament\Resources\CostBudgets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CostBudgetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider_key')->badge()->placeholder('all')->sortable(),
                TextColumn::make('pipe_app_key')->badge()->placeholder('all')->sortable(),
                TextColumn::make('period_type')->badge()->sortable(),
                TextColumn::make('amount')->money('USD')->sortable(),
                TextColumn::make('currency')->badge()->sortable(),
                IconColumn::make('is_enabled')->boolean()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
