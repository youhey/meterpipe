<?php

namespace App\Filament\Resources\AnalyticsEvents;

use App\Filament\Resources\AnalyticsEvents\Pages\ListAnalyticsEvents;
use App\Filament\Resources\AnalyticsEvents\Pages\ViewAnalyticsEvent;
use App\Filament\Resources\AnalyticsEvents\Schemas\AnalyticsEventForm;
use App\Filament\Resources\AnalyticsEvents\Schemas\AnalyticsEventInfolist;
use App\Filament\Resources\AnalyticsEvents\Tables\AnalyticsEventsTable;
use App\Models\AnalyticsEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AnalyticsEventResource extends Resource
{
    protected static ?string $model = AnalyticsEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Analytics Events';

    public static function form(Schema $schema): Schema
    {
        return AnalyticsEventForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AnalyticsEventInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AnalyticsEventsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAnalyticsEvents::route('/'),
            'view' => ViewAnalyticsEvent::route('/{record}'),
        ];
    }
}
