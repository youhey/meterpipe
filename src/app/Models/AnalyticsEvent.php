<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pipe_app_id', 'event_name', 'subject_type', 'subject_id', 'actor_type', 'actor_id_hash', 'properties', 'occurred_at'])]
class AnalyticsEvent extends Model
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
            'properties' => 'array',
            'occurred_at' => 'immutable_datetime',
        ];
    }
}
