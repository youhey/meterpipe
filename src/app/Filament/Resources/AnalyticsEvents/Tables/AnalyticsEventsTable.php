<?php

namespace App\Filament\Resources\AnalyticsEvents\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AnalyticsEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('occurred_at')->dateTime()->sortable(),
                TextColumn::make('pipeApp.key')->label('App')->searchable()->sortable(),
                TextColumn::make('event_name')->searchable()->sortable(),
                TextColumn::make('subject_type')->toggleable(),
                TextColumn::make('subject_id')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('actor_type')->toggleable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
