<?php

namespace App\Filament\Resources\CostProviders\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CostProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Provider')
                    ->schema([
                        TextInput::make('key')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Toggle::make('is_enabled')
                            ->label('Enabled'),
                        KeyValue::make('settings')
                            ->helperText('Secret はここに保存しません。API token は .env のみで管理します。')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
