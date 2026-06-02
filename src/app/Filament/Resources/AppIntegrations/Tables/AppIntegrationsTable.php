<?php

namespace App\Filament\Resources\AppIntegrations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AppIntegrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pipeApp.key')->label('App')->searchable()->sortable(),
                TextColumn::make('provider')->badge()->searchable()->sortable(),
                TextColumn::make('label')->searchable(),
                TextColumn::make('provider_project_id')->toggleable(),
                TextColumn::make('provider_resource_id')->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('enabled')->boolean()->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('provider')
                    ->options(collect(\App\Enums\IntegrationProvider::cases())->mapWithKeys(
                        fn(\App\Enums\IntegrationProvider $provider): array => [$provider->value => $provider->value],
                    )->all()),
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
