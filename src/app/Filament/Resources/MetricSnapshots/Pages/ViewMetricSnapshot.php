<?php

namespace App\Filament\Resources\MetricSnapshots\Pages;

use App\Filament\Resources\MetricSnapshots\MetricSnapshotResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMetricSnapshot extends ViewRecord
{
    protected static string $resource = MetricSnapshotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
