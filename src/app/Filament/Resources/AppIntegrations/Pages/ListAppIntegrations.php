<?php

namespace App\Filament\Resources\AppIntegrations\Pages;

use App\Filament\Resources\AppIntegrations\AppIntegrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAppIntegrations extends ListRecords
{
    protected static string $resource = AppIntegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
