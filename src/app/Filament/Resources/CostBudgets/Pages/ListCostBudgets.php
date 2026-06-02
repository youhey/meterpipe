<?php

namespace App\Filament\Resources\CostBudgets\Pages;

use App\Filament\Resources\CostBudgets\CostBudgetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCostBudgets extends ListRecords
{
    protected static string $resource = CostBudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
