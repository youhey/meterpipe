<?php

namespace App\Meterpipe\Collectors;

use RuntimeException;

final class FailingTestCollector implements MetricCollector
{
    public function name(): string
    {
        return 'failing-test';
    }

    public function collect(CollectorContext $context): CollectorResult
    {
        throw new RuntimeException('Fake collector failure');
    }
}
