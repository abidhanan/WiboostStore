<?php

namespace Tests\Feature;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class AdminUiConsistencyTest extends TestCase
{
    public function test_every_admin_paginator_uses_the_shared_transaction_pagination_view(): void
    {
        $pagination = file_get_contents(resource_path('views/vendor/pagination/tailwind.blade.php'));

        $this->assertStringContainsString('wiboost-pagination-wrap', $pagination);
        $this->assertStringContainsString('wiboost-pagination-active', $pagination);

        foreach ($this->adminBladeViews() as $path => $contents) {
            if (! str_contains($contents, '->links(')) {
                continue;
            }

            preg_match_all('/->links\s*\(([^)]*)\)/', $contents, $matches);

            foreach ($matches[1] as $arguments) {
                $arguments = trim($arguments);

                $this->assertTrue(
                    $arguments === '' || str_contains($arguments, 'vendor.pagination.tailwind'),
                    "Pagination khusus ditemukan di {$path}; semua halaman admin harus memakai desain transaksi."
                );
            }
        }
    }

    public function test_activity_log_table_keeps_combined_data_in_separate_columns(): void
    {
        $contents = file_get_contents(resource_path('views/admin/logs/index.blade.php'));

        foreach (['Nama', 'Email', 'IP', 'Status IP', 'Metode', 'URL', 'Route', 'Lokasi', 'Jaringan'] as $heading) {
            $this->assertStringContainsString(">{$heading}</th>", $contents);
        }

        $this->assertStringNotContainsString('Lokasi &amp; Jaringan</th>', $contents);
        $this->assertStringNotContainsString('>Aktivitas</th>', $contents);
        $this->assertStringContainsString('width: 340px;', $contents);
        $this->assertStringContainsString('inline-block whitespace-nowrap', $contents);
    }

    public function test_deposit_names_are_kept_on_one_line(): void
    {
        $contents = file_get_contents(resource_path('views/admin/deposits/index.blade.php'));

        $this->assertStringContainsString('min-width: 280px;', $contents);
        $this->assertStringContainsString('whitespace-nowrap font-black', $contents);
        $this->assertStringNotContainsString('text-ellipsis whitespace-nowrap font-black', $contents);
    }

    public function test_commission_reseller_names_are_one_line_without_clipping(): void
    {
        $contents = file_get_contents(resource_path('views/admin/commissions/index.blade.php'));

        $this->assertStringContainsString('min-width:340px;', $contents);
        $this->assertStringContainsString('whitespace-nowrap text-sm font-black', $contents);
        $this->assertStringNotContainsString('whitespace-normal break-words text-sm font-black', $contents);
    }

    public function test_legacy_public_urls_do_not_raise_framework_errors(): void
    {
        $this->get('/about-us')->assertRedirect('/');
        $this->get('/user/checkout/process')->assertRedirect(route('login'));
    }

    /** @return array<string, string> */
    private function adminBladeViews(): array
    {
        $views = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(resource_path('views/admin'), RecursiveDirectoryIterator::SKIP_DOTS)
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
