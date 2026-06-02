<?php

namespace App\Filament\Resources\CostDailySummaries\Pages;

use App\Filament\Resources\CostDailySummaries\CostDailySummaryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCostDailySummary extends ViewRecord
{
    protected static string $resource = CostDailySummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
