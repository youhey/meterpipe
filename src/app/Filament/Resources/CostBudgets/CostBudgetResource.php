<?php

namespace App\Filament\Resources\CostBudgets;

use App\Filament\Resources\CostBudgets\Pages\CreateCostBudget;
use App\Filament\Resources\CostBudgets\Pages\EditCostBudget;
use App\Filament\Resources\CostBudgets\Pages\ListCostBudgets;
use App\Filament\Resources\CostBudgets\Schemas\CostBudgetForm;
use App\Filament\Resources\CostBudgets\Tables\CostBudgetsTable;
use App\Models\CostBudget;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CostBudgetResource extends Resource
{
    protected static ?string $model = CostBudget::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Cost Budgets';

    public static function form(Schema $schema): Schema
    {
        return CostBudgetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CostBudgetsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCostBudgets::route('/'),
            'create' => CreateCostBudget::route('/create'),
            'edit' => EditCostBudget::route('/{record}/edit'),
        ];
    }
}
