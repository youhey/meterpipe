<?php

namespace App\Jobs;

use App\Models\CostSyncRun;
use App\Services\Costs\CostSyncService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SyncLaravelCloudCostsJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public function __construct(
        public readonly int $runId,
        public readonly CarbonImmutable $from,
        public readonly CarbonImmutable $to,
        public readonly bool $force = false,
    ) {
    }

    public function handle(CostSyncService $service): void
    {
        $service->executeLaravelCloud($this->runId, $this->from, $this->to, $this->force);
    }

    public function failed(?Throwable $throwable): void
    {
        if ($throwable === null) {
            return;
        }

        CostSyncRun::query()->whereKey($this->runId)->update([
            'status' => CostSyncRun::FAILED,
            'finished_at' => CarbonImmutable::now('UTC'),
            'error_class' => $throwable::class,
            'error_message' => mb_substr($throwable::class . ': ' . $throwable->getMessage(), 0, 500),
        ]);
    }
}
