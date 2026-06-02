<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'provider_key',
    'dimension_type',
    'external_id',
    'display_name',
    'pipe_app_key',
    'is_enabled',
    'notes',
])]
class CostDimensionMapping extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }
}
