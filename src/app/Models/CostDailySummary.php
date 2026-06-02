<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'summary_date',
    'provider_key',
    'pipe_app_key',
    'dimension_type',
    'dimension_key',
    'dimension_label',
    'amount',
    'currency',
    'record_count',
    'calculated_at',
    'summary_key',
])]
class CostDailySummary extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'summary_date' => 'immutable_date',
            'amount' => 'decimal:8',
            'calculated_at' => 'immutable_datetime',
        ];
    }
}
