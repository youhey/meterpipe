<?php

namespace App\Meterpipe\Collectors;

final readonly class CollectorResult
{
    /**
     * @param  list<array<string, mixed>>  $costDailySummaries
     * @param  list<array<string, mixed>>  $metricSnapshots
     * @param  list<array<string, mixed>>  $analyticsEvents
     */
    public function __construct(
        public int $fetchedCount,
        public array $costDailySummaries = [],
        public array $metricSnapshots = [],
        public array $analyticsEvents = [],
    ) {
    }

    public function storedCount(): int
    {
        return count($this->costDailySummaries)
            + count($this->metricSnapshots)
            + count($this->analyticsEvents);
    }
}
