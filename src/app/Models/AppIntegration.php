<?php

namespace App\Models;

use App\Enums\IntegrationProvider;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pipe_app_id', 'provider', 'provider_project_id', 'provider_api_key_id', 'provider_resource_id', 'label', 'metadata', 'enabled'])]
class AppIntegration extends Model
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
            'provider' => IntegrationProvider::class,
            'metadata' => 'array',
            'enabled' => 'boolean',
        ];
    }
}
