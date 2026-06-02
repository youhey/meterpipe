<?php

namespace App\Filament\Resources\CostDailySummaries\Pages;

use App\Filament\Resources\CostDailySummaries\CostDailySummaryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCostDailySummary extends EditRecord
{
    protected static string $resource = CostDailySummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
