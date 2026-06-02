<?php

namespace App\Filament\Resources\CostDailySummaries\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CostDailySummaryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cost daily summary')
                    ->schema([
                        TextEntry::make('date')->date(),
                        TextEntry::make('source')->badge(),
                        TextEntry::make('pipeApp.key')->label('App'),
                        TextEntry::make('service'),
                        TextEntry::make('amount')->money('USD'),
                        TextEntry::make('currency')->badge(),
                        TextEntry::make('dimensions_hash')->copyable(),
                        KeyValueEntry::make('dimensions')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
