<?php

namespace Tests\Feature;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class HeadingStructureTest extends TestCase
{
    public function test_blade_headings_are_balanced_and_use_only_three_levels(): void
    {
        foreach ($this->bladeViews() as $path => $contents) {
            preg_match_all('/<(\/?)h([1-6])\b[^>]*>/i', $contents, $matches, PREG_SET_ORDER);
            $stack = [];

            foreach ($matches as $match) {
                $level = (int) $match[2];
                $this->assertLessThanOrEqual(3, $level, "Heading terlalu dalam ditemukan di {$path}.");

                if ($match[1] === '') {
                    $stack[] = $level;
                    continue;
                }

                $this->assertNotEmpty($stack, "Tag penutup heading tanpa pembuka ditemukan di {$path}.");
                $this->assertSame(array_pop($stack), $level, "Pasangan tag heading tidak cocok di {$path}.");
            }

            $this->assertSame([], $stack, "Tag heading belum ditutup di {$path}.");
        }
    }

    public function test_blade_heading_levels_do_not_jump(): void
    {
        foreach ($this->bladeViews() as $path => $contents) {
            preg_match_all('/<h([1-3])\b/i', $contents, $matches);
            $levels = array_map('intval', $matches[1]);

            for ($index = 1; $index < count($levels); $index++) {
                $this->assertLessThanOrEqual(
                    1,
                    $levels[$index] - $levels[$index - 1],
                    "Level heading meloncat dari h{$levels[$index - 1]} ke h{$levels[$index]} di {$path}."
                );
            }
        }
    }

    public function test_primary_public_and_user_pages_define_an_h1(): void
    {
        $views = [
            'auth/admin-login.blade.php',
            'auth/confirm-password.blade.php',
            'auth/forgot-password.blade.php',
            'auth/login.blade.php',
            'auth/register.blade.php',
            'auth/reset-password.blade.php',
            'auth/verify-email.blade.php',
            'welcome.blade.php',
            'legal/show.blade.php',
            'profile/edit.blade.php',
            'public/forms/show.blade.php',
            'public/forms/thanks.blade.php',
            'user/checkout.blade.php',
            'user/dashboard.blade.php',
            'user/history.blade.php',
            'user/membership.blade.php',
            'user/order.blade.php',
            'user/order_sub.blade.php',
            'user/points/index.blade.php',
            'user/qris/show.blade.php',
            'user/report-admin.blade.php',
            'user/reseller/index.blade.php',
            'user/tutorial-category.blade.php',
            'user/tutorials.blade.php',
            'user/wallet/checkout.blade.php',
            'user/wallet/index.blade.php',
        ];

        foreach ($views as $view) {
            $contents = file_get_contents(resource_path('views/'.$view));
            $this->assertMatchesRegularExpression('/<h1\b/i', $contents, "h1 tidak ditemukan di {$view}.");
        }
    }

    public function test_admin_pages_use_the_single_layout_h1(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/admin.blade.php'));
        $this->assertSame(1, preg_match_all('/<h1\b/i', $layout));

        foreach ($this->bladeViews(resource_path('views/admin')) as $path => $contents) {
            if (str_ends_with(str_replace('\\', '/', $path), '/transactions/pdf.blade.php')) {
                continue;
            }

            $this->assertDoesNotMatchRegularExpression('/<h1\b/i', $contents, "h1 ganda ditemukan di {$path}.");
        }
    }

    /** @return array<string, string> */
    private function bladeViews(?string $root = null): array
    {
        $root ??= resource_path('views');
        $views = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $path = $file->getPathname();
            $views[$path] = (string) file_get_contents($path);
        }

        return $views;
    }
}
