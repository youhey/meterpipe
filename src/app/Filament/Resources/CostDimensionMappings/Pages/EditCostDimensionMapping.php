<?php

namespace App\Filament\Resources\CostDimensionMappings\Pages;

use App\Filament\Resources\CostDimensionMappings\CostDimensionMappingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCostDimensionMapping extends EditRecord
{
    protected static string $resource = CostDimensionMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
