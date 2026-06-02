<?php

namespace App\Services\Costs;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

class CostSyncPeriod
{
    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    public function resolve(?string $from, ?string $to, int $days): array
    {
        if ($from !== null && $to !== null) {
            return [
                CarbonImmutable::parse($from, 'UTC')->startOfDay(),
                CarbonImmutable::parse($to, 'UTC')->endOfDay(),
            ];
        }

        if ($from !== null || $to !== null) {
            throw new InvalidArgumentException('--from と --to は両方指定してください。');
        }

        $days = max(1, $days);
        $now = CarbonImmutable::now('UTC');

        return [
            $now->subDays($days - 1)->startOfDay(),
            $now->endOfDay(),
        ];
    }
}
