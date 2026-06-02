<?php

namespace App\Filament\Resources\CollectorRuns\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CollectorRunInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Collector run')
                    ->schema([
                        TextEntry::make('collector_name'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('started_at')->dateTime(),
                        TextEntry::make('finished_at')->dateTime(),
                        TextEntry::make('fetched_count'),
                        TextEntry::make('stored_count'),
                        TextEntry::make('error_message')->columnSpanFull(),
                        KeyValueEntry::make('metadata')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
