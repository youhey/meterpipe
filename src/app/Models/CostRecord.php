<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'provider_key',
    'source_record_key',
    'bucket_start',
    'bucket_end',
    'bucket_date',
    'amount',
    'currency',
    'pipe_app_key',
    'source_dimension_type',
    'external_project_id',
    'external_api_key_id',
    'external_application_id',
    'external_environment_id',
    'line_item',
    'resource_type',
    'service_name',
    'quantity',
    'unit',
    'raw_payload',
    'synced_at',
])]
class CostRecord extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'bucket_start' => 'immutable_datetime',
            'bucket_end' => 'immutable_datetime',
            'bucket_date' => 'immutable_date',
            'amount' => 'decimal:8',
            'quantity' => 'decimal:8',
            'raw_payload' => 'array',
            'synced_at' => 'immutable_datetime',
        ];
    }
}
