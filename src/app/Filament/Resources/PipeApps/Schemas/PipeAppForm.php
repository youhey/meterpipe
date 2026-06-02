<?php

namespace App\Filament\Resources\PipeApps\Schemas;

use App\Enums\PipeAppStatus;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PipeAppForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pipe app')
                    ->schema([
                        TextInput::make('key')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->required()
                            ->options(collect(PipeAppStatus::cases())->mapWithKeys(
                                fn(PipeAppStatus $status): array => [$status->value => $status->value],
                            )->all()),
                        TextInput::make('repository_url')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('base_url')
                            ->url()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->columnSpanFull(),
                        KeyValue::make('metadata')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
