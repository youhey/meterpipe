<?php

namespace App\Filament\Resources\MetricSnapshots;

use App\Filament\Resources\MetricSnapshots\Pages\ListMetricSnapshots;
use App\Filament\Resources\MetricSnapshots\Pages\ViewMetricSnapshot;
use App\Filament\Resources\MetricSnapshots\Schemas\MetricSnapshotForm;
use App\Filament\Resources\MetricSnapshots\Schemas\MetricSnapshotInfolist;
use App\Filament\Resources\MetricSnapshots\Tables\MetricSnapshotsTable;
use App\Models\MetricSnapshot;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MetricSnapshotResource extends Resource
{
    protected static ?string $model = MetricSnapshot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Metric Snapshots';

    public static function form(Schema $schema): Schema
    {
        return MetricSnapshotForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MetricSnapshotInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MetricSnapshotsTable::configure($table);
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
            'index' => ListMetricSnapshots::route('/'),
            'view' => ViewMetricSnapshot::route('/{record}'),
        ];
    }
}
