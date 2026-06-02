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

    public function testFaviconAssetsExist(): void
    {
        foreach ([
            'favicon.ico',
            'favicon-16x16.png',
            'favicon-32x32.png',
            'apple-touch-icon.png',
            'icon.svg',
            'icon-16.png',
            'icon-32.png',
            'icon-64.png',
            'icon-128.png',
            'icon-256.png',
            'icon-512.png',
        ] as $file) {
            $path = public_path($file);

            self::assertFileExists($path);
            self::assertGreaterThan(0, filesize($path));
        }
    }
}
