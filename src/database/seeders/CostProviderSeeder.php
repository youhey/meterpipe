<?php

namespace Database\Seeders;

use App\Models\CostProvider;
use Illuminate\Database\Seeder;

class CostProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            [
                'key' => CostProvider::OPENAI,
                'name' => 'OpenAI',
                'is_enabled' => config('meterpipe.openai_collector_enabled', false),
            ],
            [
                'key' => CostProvider::LARAVEL_CLOUD,
                'name' => 'Laravel Cloud',
                'is_enabled' => config('meterpipe.laravel_cloud_collector_enabled', false),
            ],
        ];

        foreach ($providers as $provider) {
            $model = CostProvider::query()->firstOrNew(['key' => $provider['key']]);
            $model->name = $provider['name'];

            if (! $model->exists) {
                $model->is_enabled = $provider['is_enabled'];
            }

            $model->save();
        }
    }
}
