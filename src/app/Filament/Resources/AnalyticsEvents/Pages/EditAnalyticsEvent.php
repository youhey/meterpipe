<?php

namespace App\Filament\Resources\AnalyticsEvents\Pages;

use App\Filament\Resources\AnalyticsEvents\AnalyticsEventResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAnalyticsEvent extends EditRecord
{
    protected static string $resource = AnalyticsEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
