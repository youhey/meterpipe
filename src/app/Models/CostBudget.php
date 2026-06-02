<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['provider_key', 'pipe_app_key', 'period_type', 'amount', 'currency', 'is_enabled'])]
class CostBudget extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'is_enabled' => 'boolean',
        ];
    }
}
