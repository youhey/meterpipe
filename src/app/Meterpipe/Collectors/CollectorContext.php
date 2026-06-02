<?php

namespace App\Meterpipe\Collectors;

use Carbon\CarbonImmutable;

final readonly class CollectorContext
{
    public function __construct(
        public bool $dryRun,
        public CarbonImmutable $now,
    ) {
    }
}
