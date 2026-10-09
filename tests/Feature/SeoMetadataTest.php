<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_has_complete_unique_indexable_metadata(): void
    {
        $html = $this->get(route('home'))
            ->assertOk()
            ->assertSee('<meta name="description"', false)
            ->assertSee('<meta property="og:title"', false)
            ->assertSee('<meta property="og:description"', false)
            ->assertSee('Platform penyedia layanan suntik sosmed, top up game, paket data operator, dan aplikasi premium dengan proses cepat, aman, dan praktis.', false)
            ->assertSee('Mulai dari SMM Panel, Top Up Game, Paket Data Operator, hingga Aplikasi Premium tersedia lengkap dalam satu platform.', false)
            ->assertDontSee('Pusat Kebutuhan Digitalmu!', false)
            ->assertSee('class="welcome-hero-logo"', false)
            ->assertSee('wiboost-share-preview-20261009-v5.jpg', false)
            ->assertSee('<meta property="og:image:type" content="image/jpeg">', false)
            ->assertSee('<meta property="og:image:width" content="1200">', false)
            ->assertSee('<meta property="og:image:height" content="630">', false)
            ->assertSee('wiboost-store-circle-20261007-v4-512.png', false)
            ->assertSee('wiboost-store-circle-20261007-v4-64.png', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('<link rel="canonical" href="'.route('home').'">', false)
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('"@type":"WebSite"', false)
            ->assertSee('"@type":"WebPage"', false)
            ->getContent();

        $this->assertSame(1, substr_count($html, '<meta name="description"'));
        $this->assertSame(1, substr_count($html, '<link rel="canonical"'));
        $this->assertStringContainsString('index,follow', $html);
        $this->assertFileExists(public_path('images/wiboost-metadata-landscape-1200x630.jpg'));
        $imageSize = getimagesize(public_path('images/wiboost-metadata-landscape-1200x630.jpg'));
        $this->assertSame(1200, $imageSize[0]);
        $this->assertSame(630, $imageSize[1]);
        $this->assertSame('image/jpeg', $imageSize['mime']);
        $this->assertFileExists(public_path('wiboost-store-circle-20261007-v4-512.png'));

        $this->get('/wiboost-share-preview-20261009-v5.jpg')
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg');
    }

    public function test_private_auth_page_is_not_indexed_or_given_public_schema(): void
    {
        $html = $this->get(route('login'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex,nofollow,noarchive,nosnippet">', false)
            ->getContent();

        $this->assertStringNotContainsString('application/ld+json', $html);
    }

    public function test_legal_page_has_its_own_description_and_canonical_url(): void
    {
        $response = $this->get(route('legal.show', 'privacy-policy'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('legal.show', 'privacy-policy').'">', false)
            ->assertSee('<meta name="description"', false);

        $this->assertStringContainsString('index,follow', $response->getContent());
    }

    public function test_manifest_and_crawler_files_are_valid(): void
    {
        $manifest = json_decode((string) file_get_contents(public_path('site.webmanifest')), true);

        $this->assertIsArray($manifest);
        $this->assertSame('Wiboost Store', $manifest['name']);
        $this->assertNotEmpty($manifest['icons']);
        $this->assertSame('/wiboost-store-circle-20261007-v4-192.png', $manifest['icons'][0]['src']);
        $this->assertSame('/wiboost-store-circle-20261007-v4-512.png', $manifest['icons'][1]['src']);

        $robots = (string) file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Disallow: /admin', $robots);
        $this->assertStringContainsString('Sitemap: https://wiboostore.com/sitemap.xml', $robots);

        $sitemap = simplexml_load_file(public_path('sitemap.xml'));
        $this->assertNotFalse($sitemap);
        $this->assertGreaterThanOrEqual(5, count($sitemap->url));
    }
}
