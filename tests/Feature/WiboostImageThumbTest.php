<?php

namespace Tests\Feature;

use App\Support\WiboostImage;
use Tests\TestCase;

class WiboostImageThumbTest extends TestCase
{
    private string $relative = 'categories/phpunit-thumb-source.png';

    protected function tearDown(): void
    {
        foreach ([
            storage_path('app/public/'.$this->relative),
            storage_path('app/public/thumbs/192/categories/phpunit-thumb-source.webp'),
        ] as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        parent::tearDown();
    }

    public function test_thumbnail_route_builds_small_webp_and_missing_source_falls_back(): void
    {
        if (! WiboostImage::canMakeThumbs()) {
            $this->markTestSkipped('GD WebP tidak tersedia.');
        }

        $directory = storage_path('app/public/categories');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $image = imagecreatetruecolor(800, 800);
        imagefilledrectangle($image, 0, 0, 800, 800, imagecolorallocate($image, 90, 118, 200));
        imagepng($image, storage_path('app/public/'.$this->relative));
        imagedestroy($image);

        $url = WiboostImage::thumb($this->relative, 192);
        $this->assertStringEndsWith('/storage/thumbs/192/categories/phpunit-thumb-source.webp', $url);

        $response = $this->get($url);
        $response->assertOk();
        $this->assertSame('image/webp', $response->headers->get('Content-Type'));

        $size = getimagesize(storage_path('app/public/thumbs/192/categories/phpunit-thumb-source.webp'));
        $this->assertSame(192, $size[0]);
        $this->assertLessThan(filesize(storage_path('app/public/'.$this->relative)), filesize(storage_path('app/public/thumbs/192/categories/phpunit-thumb-source.webp')) + 1);

        $this->assertStringEndsWith('/storage/categories/tidak-ada.png', WiboostImage::thumb('categories/tidak-ada.png', 192));
        $this->get('/storage/thumbs/192/categories/tidak-ada.webp')->assertNotFound();
    }

    public function test_fallback_css_route_serves_built_stylesheet_without_cookies(): void
    {
        $response = $this->get('/wiboost-fallback.css');
        $response->assertOk();
        $this->assertStringContainsString('text/css', (string) $response->headers->get('Content-Type'));
        $this->assertEmpty($response->headers->getCookies());
    }

    public function test_auth_pages_no_longer_inline_the_whole_stylesheet_as_fallback(): void
    {
        $html = $this->get("/login")->assertOk()->getContent();

        $this->assertStringNotContainsString("<style id=\"wiboost-vite-fallback-css\"", $html);
        $this->assertStringContainsString("wiboost-fallback.css", $html);
        $this->assertLessThan(140000, strlen($html));
    }
}