<?php

namespace App\Filament\Resources\CostDailySummaries\Pages;

use App\Filament\Resources\CostDailySummaries\CostDailySummaryResource;
use Filament\Resources\Pages\ListRecords;

class ListCostDailySummaries extends ListRecords
{
    protected static string $resource = CostDailySummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
