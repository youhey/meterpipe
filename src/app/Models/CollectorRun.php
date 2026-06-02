<?php

namespace App\Models;

use App\Enums\CollectorRunStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['collector_name', 'status', 'started_at', 'finished_at', 'fetched_count', 'stored_count', 'error_message', 'metadata'])]
class CollectorRun extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => CollectorRunStatus::class,
            'started_at' => 'immutable_datetime',
            'finished_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }
}
