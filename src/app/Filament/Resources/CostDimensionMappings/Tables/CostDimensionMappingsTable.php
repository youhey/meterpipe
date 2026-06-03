<?php

namespace App\Filament\Resources\CostDimensionMappings\Tables;

use App\Enums\CostProviderKey;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CostDimensionMappingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider_key')->badge()->searchable()->sortable(),
                TextColumn::make('dimension_type')->badge()->searchable()->sortable(),
                TextColumn::make('external_id')->searchable(),
                TextColumn::make('display_name')->searchable(),
                TextColumn::make('pipe_app_key')->badge()->searchable()->sortable(),
                IconColumn::make('is_enabled')->boolean()->sortable(),
            ])
            ->filters([
                SelectFilter::make('provider_key')
                    ->options([
                        CostProviderKey::OpenAi->value => 'openai',
                        CostProviderKey::LaravelCloud->value => 'laravel_cloud',
                    ]),
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
