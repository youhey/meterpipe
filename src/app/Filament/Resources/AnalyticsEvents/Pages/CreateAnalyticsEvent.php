<?php

namespace App\Filament\Resources\AnalyticsEvents\Pages;

use App\Filament\Resources\AnalyticsEvents\AnalyticsEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAnalyticsEvent extends CreateRecord
{
    protected static string $resource = AnalyticsEventResource::class;
}
