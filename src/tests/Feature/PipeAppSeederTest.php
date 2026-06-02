<?php

namespace Tests\Feature;

use App\Models\PipeApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PipeAppSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_pipe_apps_seeder_creates_expected_apps(): void
    {
        $this->seed();

        $this->assertSame(
            ['digestpipe', 'meterpipe', 'playpipe', 'radiopipe', 'voicepipe'],
            PipeApp::query()->orderBy('key')->pluck('key')->all(),
        );

        $this->assertDatabaseHas('pipe_apps', [
            'key' => 'digestpipe',
            'status' => 'active',
        ]);
    }
}
