<?php

namespace App\Filament\Resources\CollectorRuns\Pages;

use App\Filament\Resources\CollectorRuns\CollectorRunResource;
use Filament\Resources\Pages\ListRecords;

class ListCollectorRuns extends ListRecords
{
    protected static string $resource = CollectorRunResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
