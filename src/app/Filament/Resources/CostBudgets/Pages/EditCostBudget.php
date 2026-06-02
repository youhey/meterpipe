<?php

namespace App\Filament\Resources\CostBudgets\Pages;

use App\Filament\Resources\CostBudgets\CostBudgetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCostBudget extends EditRecord
{
    protected static string $resource = CostBudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
