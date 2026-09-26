<?php

namespace App\Console\Commands;

use App\Support\WebpImageOptimizer;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class OptimizeImagesToWebp extends Command
{
    protected $signature = 'images:optimize-webp
        {--all : Proses aset public dan storage}
        {--public : Proses aset di direktori public}
        {--storage : Proses gambar di storage/app/public}
        {--quality=82 : Kualitas WebP dari 1 sampai 100}
        {--force : Buat ulang file WebP yang sudah ada}';

    protected $description = 'Membuat WebP terkompresi dengan tetap mempertahankan gambar asli sebagai fallback.';

    public function handle(WebpImageOptimizer $optimizer): int
    {
        if (! $optimizer->supportsWebp()) {
            $this->warn('GD WebP tidak tersedia. Gambar asli tetap aman dan tidak diubah.');

            return self::SUCCESS;
        }

        $quality = max(1, min(100, (int) $this->option('quality')));
        $processAll = $this->option('all') || (! $this->option('public') && ! $this->option('storage'));
        $roots = [];

        if ($processAll || $this->option('public')) {
            $roots[] = public_path();
        }

        if ($processAll || $this->option('storage')) {
            $roots[] = storage_path('app/public');
        }

        $converted = 0;
        $skipped = 0;
        $failed = 0;
        $seen = [];

        foreach (array_unique($roots) as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->isLink()) {
                    continue;
                }

                $sourcePath = $file->getPathname();
                $realPath = realpath($sourcePath) ?: $sourcePath;
                if (isset($seen[$realPath])) {
                    continue;
                }
                $seen[$realPath] = true;

                $extension = strtolower($file->getExtension());
                if (! in_array($extension, ['bmp', 'gif', 'jpeg', 'jpg', 'png', 'wbmp'], true)) {
                    continue;
                }

                $targetPath = $sourcePath.'.webp';
                $wasFresh = is_file($targetPath)
                    && filemtime($targetPath) >= filemtime($sourcePath)
                    && ! $this->option('force');

                $result = $optimizer->createNegotiatedSibling(
                    $sourcePath,
                    $quality,
                    (bool) $this->option('force')
                );

                if ($result === null) {
                    $failed++;
                } elseif ($wasFresh) {
                    $skipped++;
                } else {
                    $converted++;
                }
            }
        }

        $this->info("WebP selesai: {$converted} dibuat, {$skipped} sudah terbaru, {$failed} gagal/dilewati.");
        $this->line('Gambar asli dipertahankan sebagai fallback agar tidak menimbulkan gambar rusak atau 404.');

        return self::SUCCESS;
    }
}
