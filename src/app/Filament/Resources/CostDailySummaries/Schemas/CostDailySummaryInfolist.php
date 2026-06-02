<?php

namespace App\Filament\Resources\CostDailySummaries\Schemas;

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
                        TextEntry::make('summary_date')->date(),
                        TextEntry::make('provider_key')->badge(),
                        TextEntry::make('pipe_app_key')->label('App')->badge(),
                        TextEntry::make('dimension_type')->badge(),
                        TextEntry::make('dimension_key')->copyable(),
                        TextEntry::make('dimension_label'),
                        TextEntry::make('amount')->money('USD'),
                        TextEntry::make('currency')->badge(),
                        TextEntry::make('record_count')->numeric(),
                        TextEntry::make('calculated_at')->dateTime(),
                        TextEntry::make('summary_key')->copyable()->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
