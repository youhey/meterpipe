<?php

namespace App\Meterpipe\Collectors;

interface MetricCollector
{
    public function name(): string;

    public function collect(CollectorContext $context): CollectorResult;
}
