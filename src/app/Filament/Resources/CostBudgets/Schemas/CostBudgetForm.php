<?php

namespace App\Filament\Resources\CostBudgets\Schemas;

use App\Models\CostProvider;
use App\Models\PipeApp;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CostBudgetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Budget')
                    ->schema([
                        Select::make('provider_key')
                            ->options([
                                CostProvider::OPENAI => 'openai',
                                CostProvider::LARAVEL_CLOUD => 'laravel_cloud',
                            ])
                            ->placeholder('all'),
                        Select::make('pipe_app_key')
                            ->options(fn(): array => PipeApp::query()->pluck('name', 'key')->all())
                            ->placeholder('all')
                            ->searchable(),
                        Select::make('period_type')
                            ->required()
                            ->options(['monthly' => 'monthly'])
                            ->default('monthly'),
                        TextInput::make('amount')
                            ->required()
                            ->numeric(),
                        TextInput::make('currency')
                            ->required()
                            ->maxLength(8)
                            ->default('usd'),
                        Toggle::make('is_enabled')
                            ->label('Enabled')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
