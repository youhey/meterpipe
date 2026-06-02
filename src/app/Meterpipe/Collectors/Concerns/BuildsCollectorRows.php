<?php

namespace App\Meterpipe\Collectors\Concerns;

trait BuildsCollectorRows
{
    /** @param array<string, mixed>|null $dimensions */
    private function dimensionsHash(?array $dimensions): string
    {
        if ($dimensions === null || $dimensions === []) {
            return hash('sha256', '{}');
        }

        ksort($dimensions);

        return hash('sha256', (string) json_encode($dimensions, JSON_THROW_ON_ERROR));
    }
}
