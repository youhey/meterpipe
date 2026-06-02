<?php

namespace App\Filament\Resources\CostSyncRuns\Pages;

use App\Filament\Resources\CostSyncRuns\CostSyncRunResource;
use Filament\Resources\Pages\ListRecords;

class ListCostSyncRuns extends ListRecords
{
    protected static string $resource = CostSyncRunResource::class;
}
