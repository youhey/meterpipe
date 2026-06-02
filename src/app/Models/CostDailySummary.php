<?php

namespace App\Models;

use App\Enums\MetricSource;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['source', 'pipe_app_id', 'service', 'amount', 'currency', 'dimensions', 'dimensions_hash', 'date'])]
class CostDailySummary extends Model
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
            'amount' => 'decimal:8',
            'dimensions' => 'array',
            'date' => 'immutable_date',
        ];
    }
}
