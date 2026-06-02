<?php

namespace App\Filament\Resources\CollectorRuns\Pages;

use App\Filament\Resources\CollectorRuns\CollectorRunResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCollectorRun extends ViewRecord
{
    protected static string $resource = CollectorRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
