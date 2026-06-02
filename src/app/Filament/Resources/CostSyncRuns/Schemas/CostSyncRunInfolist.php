<?php

namespace App\Filament\Resources\CostSyncRuns\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CostSyncRunInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sync run')
                    ->schema([
                        TextEntry::make('provider_key')->badge(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('scope')->badge(),
                        TextEntry::make('period_start')->dateTime(),
                        TextEntry::make('period_end')->dateTime(),
                        TextEntry::make('started_at')->dateTime(),
                        TextEntry::make('finished_at')->dateTime(),
                        TextEntry::make('records_fetched')->numeric(),
                        TextEntry::make('records_saved')->numeric(),
                        TextEntry::make('error_class'),
                        TextEntry::make('error_message')->columnSpanFull(),
                        KeyValueEntry::make('meta')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
