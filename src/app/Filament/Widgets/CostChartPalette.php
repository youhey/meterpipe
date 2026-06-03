<?php

namespace App\Filament\Widgets;

class CostChartPalette
{
    private const COLORS = [
        '#2563eb',
        '#16a34a',
        '#dc2626',
        '#9333ea',
        '#0891b2',
        '#ca8a04',
        '#db2777',
        '#4f46e5',
        '#059669',
        '#ea580c',
        '#7c3aed',
        '#0d9488',
    ];

    /** @return list<string> */
    public static function colors(int $count): array
    {
        $colors = [];

        for ($index = 0; $index < $count; $index++) {
            $colors[] = self::COLORS[$index % count(self::COLORS)];
        }

        return $colors;
    }

    public static function color(int $index): string
    {
        return self::COLORS[$index % count(self::COLORS)];
    }

    /** @return list<string> */
    public static function translucentColors(int $count): array
    {
        return array_map(fn(string $color): string => $color . '33', self::colors($count));
    }

    public static function translucentColor(int $index): string
    {
        return self::color($index) . '33';
    }
}
