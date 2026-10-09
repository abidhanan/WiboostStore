<?php

namespace Tests\Feature;

use Tests\TestCase;

class SplitCssBundlesTest extends TestCase
{
    public function test_manifest_has_separate_public_and_admin_stylesheets(): void
    {
        $manifest = json_decode((string) file_get_contents(public_path('build/manifest.json')), true);

        $this->assertArrayHasKey('resources/css/app.css', $manifest);
        $this->assertArrayHasKey('resources/css/admin.css', $manifest);
        $this->assertNotSame($manifest['resources/css/app.css']['file'], $manifest['resources/css/admin.css']['file']);

        $public = (string) file_get_contents(public_path('build/'.$manifest['resources/css/app.css']['file']));
        $admin = (string) file_get_contents(public_path('build/'.$manifest['resources/css/admin.css']['file']));

        $this->assertLessThan(strlen($admin), strlen($public));
    }

    public function test_public_pages_use_app_css_and_admin_layout_uses_admin_css(): void
    {
        $manifest = json_decode((string) file_get_contents(public_path('build/manifest.json')), true);
        $appFile = $manifest['resources/css/app.css']['file'];
        $adminFile = $manifest['resources/css/admin.css']['file'];

        $login = $this->get(route('login'))->assertOk()->getContent();
        $this->assertStringContainsString($appFile, $login);
        $this->assertStringNotContainsString($adminFile, $login);

        $layout = (string) file_get_contents(resource_path('views/layouts/admin.blade.php'));
        $this->assertStringContainsString("@vite(['resources/css/admin.css', 'resources/js/app.js'])", $layout);
        $this->assertStringNotContainsString("@vite(['resources/css/app.css'", $layout);
    }

    public function test_fallback_route_serves_the_requested_bundle(): void
    {
        $manifest = json_decode((string) file_get_contents(public_path('build/manifest.json')), true);

        $app = $this->get('/wiboost-fallback.css')->assertOk();
        $admin = $this->get('/wiboost-fallback.css?e=admin')->assertOk();

        $this->assertSame(
            filesize(public_path('build/'.$manifest['resources/css/app.css']['file'])),
            strlen(file_get_contents($app->baseResponse->getFile()->getPathname()))
        );
        $this->assertSame(
            filesize(public_path('build/'.$manifest['resources/css/admin.css']['file'])),
            strlen(file_get_contents($admin->baseResponse->getFile()->getPathname()))
        );
    }
}
