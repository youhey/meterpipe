<?php

namespace Database\Seeders;

use App\Enums\PipeAppStatus;
use App\Models\PipeApp;
use Illuminate\Database\Seeder;

class PipeAppSeeder extends Seeder
{
    public function run(): void
    {
        $apps = [
            ['key' => 'digestpipe', 'name' => 'digestpipe', 'status' => PipeAppStatus::Active],
            ['key' => 'radiopipe', 'name' => 'radiopipe', 'status' => PipeAppStatus::Active],
            ['key' => 'voicepipe', 'name' => 'voicepipe', 'status' => PipeAppStatus::Planned],
            ['key' => 'playpipe', 'name' => 'playpipe', 'status' => PipeAppStatus::Planned],
            ['key' => 'meterpipe', 'name' => 'meterpipe', 'status' => PipeAppStatus::Active],
        ];

        foreach ($apps as $app) {
            PipeApp::query()->updateOrCreate(
                ['key' => $app['key']],
                [
                    'name' => $app['name'],
                    'status' => $app['status']->value,
                ],
            );
        }
    }
}
