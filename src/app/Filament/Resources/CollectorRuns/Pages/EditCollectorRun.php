<?php

namespace App\Filament\Resources\CollectorRuns\Pages;

use App\Filament\Resources\CollectorRuns\CollectorRunResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCollectorRun extends EditRecord
{
    protected static string $resource = CollectorRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
