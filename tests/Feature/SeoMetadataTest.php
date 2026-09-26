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
            ->assertSee('images/wiboost-metadata-logo.webp?v=20260921-metadata', false)
            ->assertSee('<meta property="og:image:type" content="image/webp">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('<link rel="canonical" href="'.route('home').'">', false)
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('"@type":"WebSite"', false)
            ->assertSee('"@type":"WebPage"', false)
            ->getContent();

        $this->assertSame(1, substr_count($html, '<meta name="description"'));
        $this->assertSame(1, substr_count($html, '<link rel="canonical"'));
        $this->assertStringContainsString('index,follow', $html);
        $this->assertFileExists(public_path('images/wiboost-metadata-logo.webp'));
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

        $robots = (string) file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Disallow: /admin', $robots);
        $this->assertStringContainsString('Sitemap: https://wiboostore.com/sitemap.xml', $robots);

        $sitemap = simplexml_load_file(public_path('sitemap.xml'));
        $this->assertNotFalse($sitemap);
        $this->assertGreaterThanOrEqual(5, count($sitemap->url));
    }
}
