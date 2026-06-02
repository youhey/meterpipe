<?php

namespace App\Filament\Resources\CostDimensionMappings;

use App\Filament\Resources\CostDimensionMappings\Pages\CreateCostDimensionMapping;
use App\Filament\Resources\CostDimensionMappings\Pages\EditCostDimensionMapping;
use App\Filament\Resources\CostDimensionMappings\Pages\ListCostDimensionMappings;
use App\Filament\Resources\CostDimensionMappings\Schemas\CostDimensionMappingForm;
use App\Filament\Resources\CostDimensionMappings\Tables\CostDimensionMappingsTable;
use App\Models\CostDimensionMapping;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CostDimensionMappingResource extends Resource
{
    protected static ?string $model = CostDimensionMapping::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?string $navigationLabel = 'Cost Dimension Mappings';

    public static function form(Schema $schema): Schema
    {
        return CostDimensionMappingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CostDimensionMappingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCostDimensionMappings::route('/'),
            'create' => CreateCostDimensionMapping::route('/create'),
            'edit' => EditCostDimensionMapping::route('/{record}/edit'),
        ];
    }
}
