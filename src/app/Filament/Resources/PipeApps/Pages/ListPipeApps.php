<?php

namespace App\Filament\Resources\PipeApps\Pages;

use App\Filament\Resources\PipeApps\PipeAppResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPipeApps extends ListRecords
{
    protected static string $resource = PipeAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
