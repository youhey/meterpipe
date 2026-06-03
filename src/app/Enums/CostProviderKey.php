<?php

namespace App\Enums;

enum CostProviderKey: string
{
    case OpenAi = 'openai';
    case LaravelCloud = 'laravel_cloud';
    case All = 'all';

    public function label(): string
    {
        return match ($this) {
            self::OpenAi => 'OpenAI',
            self::LaravelCloud => 'Laravel Cloud',
            self::All => 'All Providers',
        };
    }

    public function enabledConfigPath(): string
    {
        return match ($this) {
            self::OpenAi => 'meterpipe.cost_providers.openai.enabled',
            self::LaravelCloud => 'meterpipe.cost_providers.laravel_cloud.enabled',
            self::All => 'meterpipe.cost_providers.all.enabled',
        };
    }

    /** @return list<self> */
    public static function syncable(): array
    {
        return [
            self::OpenAi,
            self::LaravelCloud,
        ];
    }

    /** @return list<string> */
    public static function syncableValues(): array
    {
        return array_map(
            static fn(self $provider): string => $provider->value,
            self::syncable(),
        );
    }

    /** @return list<string> */
    public static function commandValues(): array
    {
        return [
            self::OpenAi->value,
            self::LaravelCloud->value,
            self::All->value,
        ];
    }
}
