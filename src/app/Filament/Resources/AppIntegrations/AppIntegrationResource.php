<?php

namespace App\Filament\Resources\AppIntegrations;

use App\Filament\Resources\AppIntegrations\Pages\CreateAppIntegration;
use App\Filament\Resources\AppIntegrations\Pages\EditAppIntegration;
use App\Filament\Resources\AppIntegrations\Pages\ListAppIntegrations;
use App\Filament\Resources\AppIntegrations\Schemas\AppIntegrationForm;
use App\Filament\Resources\AppIntegrations\Tables\AppIntegrationsTable;
use App\Models\AppIntegration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AppIntegrationResource extends Resource
{
    protected static ?string $model = AppIntegration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'App Integrations';

    public static function form(Schema $schema): Schema
    {
        return AppIntegrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppIntegrationsTable::configure($table);
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
            'index' => ListAppIntegrations::route('/'),
            'create' => CreateAppIntegration::route('/create'),
            'edit' => EditAppIntegration::route('/{record}/edit'),
        ];
    }
}
