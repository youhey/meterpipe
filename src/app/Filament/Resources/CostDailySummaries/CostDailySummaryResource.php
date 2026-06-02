<?php

namespace App\Filament\Resources\CostDailySummaries;

use App\Filament\Resources\CostDailySummaries\Pages\ListCostDailySummaries;
use App\Filament\Resources\CostDailySummaries\Pages\ViewCostDailySummary;
use App\Filament\Resources\CostDailySummaries\Schemas\CostDailySummaryForm;
use App\Filament\Resources\CostDailySummaries\Schemas\CostDailySummaryInfolist;
use App\Filament\Resources\CostDailySummaries\Tables\CostDailySummariesTable;
use App\Models\CostDailySummary;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CostDailySummaryResource extends Resource
{
    protected static ?string $model = CostDailySummary::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Cost Daily Summaries';

    public static function form(Schema $schema): Schema
    {
        return CostDailySummaryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CostDailySummaryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CostDailySummariesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCostDailySummaries::route('/'),
            'view' => ViewCostDailySummary::route('/{record}'),
        ];
    }
}
