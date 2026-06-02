<?php

namespace App\Filament\Resources\CostSyncRuns;

use App\Filament\Resources\CostSyncRuns\Pages\ListCostSyncRuns;
use App\Filament\Resources\CostSyncRuns\Pages\ViewCostSyncRun;
use App\Filament\Resources\CostSyncRuns\Schemas\CostSyncRunInfolist;
use App\Filament\Resources\CostSyncRuns\Tables\CostSyncRunsTable;
use App\Models\CostSyncRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CostSyncRunResource extends Resource
{
    protected static ?string $model = CostSyncRun::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $navigationLabel = 'Cost Sync Runs';

    public static function infolist(Schema $schema): Schema
    {
        return CostSyncRunInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CostSyncRunsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCostSyncRuns::route('/'),
            'view' => ViewCostSyncRun::route('/{record}'),
        ];
    }
}
