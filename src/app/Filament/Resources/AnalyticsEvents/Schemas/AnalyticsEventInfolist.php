<?php

namespace App\Filament\Resources\AnalyticsEvents\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AnalyticsEventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Analytics event')
                    ->schema([
                        TextEntry::make('occurred_at')->dateTime(),
                        TextEntry::make('pipeApp.key')->label('App'),
                        TextEntry::make('event_name')->badge(),
                        TextEntry::make('subject_type'),
                        TextEntry::make('subject_id'),
                        TextEntry::make('actor_type'),
                        TextEntry::make('actor_id_hash')->copyable(),
                        KeyValueEntry::make('properties')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
