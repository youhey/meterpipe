<?php

namespace App\Meterpipe\Collectors;

use App\Models\PipeApp;

final class FakePipeAnalyticsCollector implements MetricCollector
{
    public function name(): string
    {
        return 'fake-pipe-analytics';
    }

    public function collect(CollectorContext $context): CollectorResult
    {
        $apps = PipeApp::query()->whereIn('key', ['digestpipe', 'radiopipe', 'voicepipe', 'playpipe'])->get();
        $metrics = [];
        $events = [];

        foreach ($apps as $index => $app) {
            $metrics[] = [
                'source' => $app->key,
                'pipe_app_id' => $app->id,
                'metric_name' => 'app.requests',
                'value' => 100 + ($index * 25),
                'unit' => 'request',
                'dimensions' => ['demo' => true],
                'measured_at' => $context->now,
            ];

            $events[] = [
                'pipe_app_id' => $app->id,
                'event_name' => 'demo.pipeline.completed',
                'subject_type' => 'demo',
                'subject_id' => 'sample-' . $app->key,
                'actor_type' => 'system',
                'actor_id_hash' => hash('sha256', 'demo-system'),
                'properties' => ['demo' => true, 'step_count' => 3 + $index],
                'occurred_at' => $context->now->subMinutes($index * 12),
            ];
        }

        return new CollectorResult(
            fetchedCount: count($metrics) + count($events),
            metricSnapshots: $metrics,
            analyticsEvents: $events,
        );
    }
}
