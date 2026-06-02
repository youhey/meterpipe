<?php

namespace App\Filament\Resources\MetricSnapshots\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MetricSnapshotsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('measured_at')->dateTime()->sortable(),
                TextColumn::make('source')->badge()->searchable()->sortable(),
                TextColumn::make('pipeApp.key')->label('App')->searchable()->sortable(),
                TextColumn::make('metric_name')->searchable()->sortable(),
                TextColumn::make('value')->numeric(decimalPlaces: 4)->sortable(),
                TextColumn::make('unit')->badge()->sortable(),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->options([
                        'openai' => 'openai',
                        'laravel_cloud' => 'laravel_cloud',
                        'digestpipe' => 'digestpipe',
                        'radiopipe' => 'radiopipe',
                        'voicepipe' => 'voicepipe',
                        'playpipe' => 'playpipe',
                        'meterpipe' => 'meterpipe',
                        'manual' => 'manual',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
