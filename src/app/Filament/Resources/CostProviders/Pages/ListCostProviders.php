<?php

namespace App\Filament\Resources\CostProviders\Pages;

use App\Filament\Resources\CostProviders\CostProviderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCostProviders extends ListRecords
{
    protected static string $resource = CostProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
