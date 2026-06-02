<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * @internal
 */
class RootRouteTest extends TestCase
{
    public function testRootRouteReturnsCacheablePlainTextNotFound(): void
    {
        $response = $this->get('/');

        $response->assertNotFound()
            ->assertHeader('Cache-Control', 'max-age=3600, public, s-maxage=86400')
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSeeText('Not Found')
            ->assertDontSeeText('Laravel');
    }
}
