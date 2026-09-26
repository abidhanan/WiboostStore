<?php

namespace Tests\Feature;

use App\Support\WebpImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebpImageOptimizationTest extends TestCase
{
    public function test_uploaded_raster_image_is_stored_as_a_compressed_webp(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('Dukungan GD WebP tidak tersedia.');
        }

        Storage::fake('public');

        $path = app(WebpImageOptimizer::class)->storeUploadedFile(
            UploadedFile::fake()->image('banner.png', 320, 180),
            'banners'
        );

        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertMissing(substr($path, 0, -5).'.png');
        $this->assertSame('image/webp', mime_content_type(Storage::disk('public')->path($path)));
        $this->assertGreaterThan(0, Storage::disk('public')->size($path));
    }

    public function test_non_image_upload_remains_untouched(): void
    {
        Storage::fake('public');

        $path = app(WebpImageOptimizer::class)->storeUploadedFile(
            UploadedFile::fake()->create('dokumen.pdf', 20, 'application/pdf'),
            'documents'
        );

        Storage::disk('public')->assertExists($path);
        $this->assertFileDoesNotExist(Storage::disk('public')->path($path).'.webp');
    }
}
