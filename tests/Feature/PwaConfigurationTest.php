<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaConfigurationTest extends TestCase
{
    public function test_manifest_defines_the_installable_application_and_icons(): void
    {
        $manifest = json_decode(
            file_get_contents(public_path('manifest.json')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertSame('YELENSCOL - Gestion scolaire', $manifest['name']);
        $this->assertSame('YELENSCOL', $manifest['short_name']);
        $this->assertNotEmpty($manifest['description']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('#1e1b6b', $manifest['theme_color']);
        $this->assertSame('#f1f5f9', $manifest['background_color']);
        $this->assertSame(
            [
                ['src' => '/pwa-icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/pwa-icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
            ],
            $manifest['icons']
        );

        foreach ([192, 512] as $size) {
            $icon = getimagesize(public_path("pwa-icon-{$size}.png"));

            $this->assertNotFalse($icon);
            $this->assertSame([$size, $size], [$icon[0], $icon[1]]);
        }
    }
}
