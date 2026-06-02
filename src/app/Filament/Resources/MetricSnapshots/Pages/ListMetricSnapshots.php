<?php

namespace App\Filament\Resources\MetricSnapshots\Pages;

use App\Filament\Resources\MetricSnapshots\MetricSnapshotResource;
use Filament\Resources\Pages\ListRecords;

class ListMetricSnapshots extends ListRecords
{
    protected static string $resource = MetricSnapshotResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
