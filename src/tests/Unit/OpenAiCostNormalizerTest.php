<?php

namespace Tests\Unit;

use App\Models\CostProvider;
use App\Services\CostProviders\OpenAi\OpenAiCostNormalizer;
use Tests\TestCase;

class OpenAiCostNormalizerTest extends TestCase
{
    public function test_normalizes_cost_bucket_records(): void
    {
        $records = app(OpenAiCostNormalizer::class)->normalize([[
            'data' => [[
                'start_time' => 1_780_272_000,
                'end_time' => 1_780_358_400,
                'results' => [[
                    'amount' => ['value' => 12.345, 'currency' => 'usd'],
                    'project_id' => 'proj_digest',
                    'api_key_id' => null,
                    'line_item' => 'responses',
                ]],
            ]],
        ]], 'project_id');

        $this->assertCount(1, $records);
        $this->assertSame(CostProvider::OPENAI, $records[0]['provider_key']);
        $this->assertSame('proj_digest', $records[0]['external_project_id']);
        $this->assertSame('12.34500000', $records[0]['amount']);
        $this->assertSame($records[0]['source_record_key'], app(OpenAiCostNormalizer::class)->normalize([[
            'data' => [[
                'start_time' => 1_780_272_000,
                'end_time' => 1_780_358_400,
                'results' => [[
                    'amount' => ['value' => 99.999, 'currency' => 'usd'],
                    'project_id' => 'proj_digest',
                    'api_key_id' => null,
                    'line_item' => 'responses',
                ]],
            ]],
        ]], 'project_id')[0]['source_record_key']);
    }
}
