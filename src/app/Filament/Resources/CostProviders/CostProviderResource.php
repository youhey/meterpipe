<?php

namespace App\Filament\Resources\CostProviders;

use App\Filament\Resources\CostProviders\Pages\CreateCostProvider;
use App\Filament\Resources\CostProviders\Pages\EditCostProvider;
use App\Filament\Resources\CostProviders\Pages\ListCostProviders;
use App\Filament\Resources\CostProviders\Schemas\CostProviderForm;
use App\Filament\Resources\CostProviders\Tables\CostProvidersTable;
use App\Models\CostProvider;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CostProviderResource extends Resource
{
    protected static ?string $model = CostProvider::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;

    protected static ?string $navigationLabel = 'Cost Providers';

    public static function form(Schema $schema): Schema
    {
        return CostProviderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CostProvidersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCostProviders::route('/'),
            'create' => CreateCostProvider::route('/create'),
            'edit' => EditCostProvider::route('/{record}/edit'),
        ];
    }
}
