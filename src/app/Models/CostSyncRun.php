<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'provider_key',
    'status',
    'scope',
    'period_start',
    'period_end',
    'started_at',
    'finished_at',
    'records_fetched',
    'records_saved',
    'error_class',
    'error_message',
    'meta',
])]
class CostSyncRun extends Model
{
    public const QUEUED = 'queued';

    public const RUNNING = 'running';

    public const SUCCEEDED = 'succeeded';

    public const FAILED = 'failed';

    public const SKIPPED = 'skipped';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'period_start' => 'immutable_datetime',
            'period_end' => 'immutable_datetime',
            'started_at' => 'immutable_datetime',
            'finished_at' => 'immutable_datetime',
            'meta' => 'array',
        ];
    }
}
