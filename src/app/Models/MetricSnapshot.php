<?php

namespace App\Models;

use App\Enums\MetricSource;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['source', 'pipe_app_id', 'metric_name', 'value', 'unit', 'dimensions', 'measured_at'])]
class MetricSnapshot extends Model
{
    /** @return BelongsTo<PipeApp, $this> */
    public function pipeApp(): BelongsTo
    {
        return $this->belongsTo(PipeApp::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'source' => MetricSource::class,
            'value' => 'decimal:8',
            'dimensions' => 'array',
            'measured_at' => 'immutable_datetime',
        ];
    }
}
