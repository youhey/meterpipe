<?php

namespace App\Filament\Resources\CostDimensionMappings\Pages;

use App\Filament\Resources\CostDimensionMappings\CostDimensionMappingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCostDimensionMappings extends ListRecords
{
    protected static string $resource = CostDimensionMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
