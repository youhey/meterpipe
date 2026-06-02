<?php

namespace App\Filament\Resources\AppIntegrations\Schemas;

use App\Enums\IntegrationProvider;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppIntegrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Integration mapping')
                    ->schema([
                        Select::make('pipe_app_id')
                            ->relationship('pipeApp', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('provider')
                            ->required()
                            ->options(collect(IntegrationProvider::cases())->mapWithKeys(
                                fn(IntegrationProvider $provider): array => [$provider->value => $provider->value],
                            )->all()),
                        TextInput::make('label')->maxLength(255),
                        Toggle::make('enabled')->default(true),
                        TextInput::make('provider_project_id')->maxLength(255),
                        TextInput::make('provider_api_key_id')->maxLength(255),
                        TextInput::make('provider_resource_id')->maxLength(255),
                        KeyValue::make('metadata')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
