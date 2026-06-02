<?php

namespace App\Filament\Resources\PipeApps;

use App\Filament\Resources\PipeApps\Pages\CreatePipeApp;
use App\Filament\Resources\PipeApps\Pages\EditPipeApp;
use App\Filament\Resources\PipeApps\Pages\ListPipeApps;
use App\Filament\Resources\PipeApps\Schemas\PipeAppForm;
use App\Filament\Resources\PipeApps\Tables\PipeAppsTable;
use App\Models\PipeApp;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PipeAppResource extends Resource
{
    protected static ?string $model = PipeApp::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Pipe Apps';

    public static function form(Schema $schema): Schema
    {
        return PipeAppForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PipeAppsTable::configure($table);
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
            'index' => ListPipeApps::route('/'),
            'create' => CreatePipeApp::route('/create'),
            'edit' => EditPipeApp::route('/{record}/edit'),
        ];
    }
}
