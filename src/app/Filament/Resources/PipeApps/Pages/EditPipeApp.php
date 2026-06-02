<?php

namespace App\Filament\Resources\PipeApps\Pages;

use App\Filament\Resources\PipeApps\PipeAppResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPipeApp extends EditRecord
{
    protected static string $resource = PipeAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
