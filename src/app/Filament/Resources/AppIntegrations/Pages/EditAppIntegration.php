<?php

namespace App\Filament\Resources\AppIntegrations\Pages;

use App\Filament\Resources\AppIntegrations\AppIntegrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAppIntegration extends EditRecord
{
    protected static string $resource = AppIntegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
