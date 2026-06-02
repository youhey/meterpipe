<?php

namespace App\Filament\Resources\MetricSnapshots\Pages;

use App\Filament\Resources\MetricSnapshots\MetricSnapshotResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMetricSnapshot extends EditRecord
{
    protected static string $resource = MetricSnapshotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
