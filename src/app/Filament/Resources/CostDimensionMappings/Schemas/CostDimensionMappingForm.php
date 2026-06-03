<?php

namespace App\Filament\Resources\CostDimensionMappings\Schemas;

use App\Enums\CostProviderKey;
use App\Models\PipeApp;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CostDimensionMappingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Mapping')
                    ->schema([
                        Select::make('provider_key')
                            ->required()
                            ->options([
                                CostProviderKey::OpenAi->value => 'openai',
                                CostProviderKey::LaravelCloud->value => 'laravel_cloud',
                            ]),
                        Select::make('dimension_type')
                            ->required()
                            ->options([
                                'project' => 'project',
                                'api_key' => 'api_key',
                                'application' => 'application',
                                'environment' => 'environment',
                                'line_item' => 'line_item',
                                'resource_type' => 'resource_type',
                            ]),
                        TextInput::make('external_id')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('display_name')
                            ->maxLength(255),
                        Select::make('pipe_app_key')
                            ->options(fn(): array => PipeApp::query()->pluck('name', 'key')->all())
                            ->searchable(),
                        Toggle::make('is_enabled')
                            ->label('Enabled')
                            ->default(true),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
