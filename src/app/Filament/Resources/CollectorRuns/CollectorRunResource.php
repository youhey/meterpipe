<?php

namespace App\Filament\Resources\CollectorRuns;

use App\Filament\Resources\CollectorRuns\Pages\ListCollectorRuns;
use App\Filament\Resources\CollectorRuns\Pages\ViewCollectorRun;
use App\Filament\Resources\CollectorRuns\Schemas\CollectorRunForm;
use App\Filament\Resources\CollectorRuns\Schemas\CollectorRunInfolist;
use App\Filament\Resources\CollectorRuns\Tables\CollectorRunsTable;
use App\Models\CollectorRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CollectorRunResource extends Resource
{
    protected static ?string $model = CollectorRun::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Collector Runs';

    public static function form(Schema $schema): Schema
    {
        return CollectorRunForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CollectorRunInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CollectorRunsTable::configure($table);
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
            'index' => ListCollectorRuns::route('/'),
            'view' => ViewCollectorRun::route('/{record}'),
        ];
    }
}
