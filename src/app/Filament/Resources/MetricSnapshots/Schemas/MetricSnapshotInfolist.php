<?php

namespace App\Filament\Resources\MetricSnapshots\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MetricSnapshotInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Metric snapshot')
                    ->schema([
                        TextEntry::make('source')->badge(),
                        TextEntry::make('pipeApp.key')->label('App'),
                        TextEntry::make('metric_name'),
                        TextEntry::make('value'),
                        TextEntry::make('unit')->badge(),
                        TextEntry::make('measured_at')->dateTime(),
                        KeyValueEntry::make('dimensions')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
