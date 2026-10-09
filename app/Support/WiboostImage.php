<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Thumbnail WebP untuk gambar kategori/produk yang tampil kecil (ikon 48-128 px) agar halaman tidak
 * mengunduh gambar asli ratusan KB. File dibuat sekali (saat pertama diminta) di storage/app/public/thumbs.
 */
class WiboostImage
{
    public const SIZES = [96, 128, 192, 256, 384];

    private const SOURCE_PATH_PATTERN = '#^[A-Za-z0-9_\-]+(/[A-Za-z0-9_\-]+)*/[A-Za-z0-9_\-.]+\.(jpe?g|png|webp|gif)$#i';

    /**
     * URL thumbnail; jatuh kembali ke URL gambar asli bila WebP/GD tidak tersedia atau path tidak valid.
     */
    public static function thumb(?string $storagePath, int $size = 192): string
    {
        $storagePath = ltrim((string) $storagePath, '/');

        if ($storagePath === '' || ! in_array($size, self::SIZES, true) || ! self::canMakeThumbs()) {
            return $storagePath === '' ? '' : Storage::url($storagePath);
        }

        if (! preg_match(self::SOURCE_PATH_PATTERN, $storagePath) || str_contains($storagePath, '..')) {
            return Storage::url($storagePath);
        }

        if (! is_file(storage_path('app/public/' . $storagePath))) {
            return Storage::url($storagePath);
        }

        return Storage::url(self::thumbRelativePath($storagePath, $size));
    }

    public static function canMakeThumbs(): bool
    {
        return extension_loaded('gd') && function_exists('imagewebp') && function_exists('imagecreatefromstring');
    }

    public static function thumbRelativePath(string $storagePath, int $size): string
    {
        $withoutExtension = preg_replace('/\.[^.\/]+$/', '', $storagePath);

        return "thumbs/{$size}/{$withoutExtension}.webp";
    }

    public static function findSource(string $base): ?string
    {
        if (str_contains($base, '..') || ! preg_match('#^[A-Za-z0-9_\-]+(/[A-Za-z0-9_\-]+)*/[A-Za-z0-9_\-.]+$#', $base)) {
            return null;
        }

        $root = storage_path('app/public');
        foreach (['jpg', 'jpeg', 'png', 'webp', 'gif'] as $extension) {
            if (is_file("{$root}/{$base}.{$extension}")) {
                return "{$root}/{$base}.{$extension}";
            }
        }

        return null;
    }

    /**
     * Buat thumbnail dari path relatif thumbs/{size}/{dir}/{name}.webp. Mengembalikan path file absolut atau null.
     */
    public static function generate(int $size, string $relativeWithoutThumbPrefix): ?string
    {
        if (! in_array($size, self::SIZES, true) || ! self::canMakeThumbs() || str_contains($relativeWithoutThumbPrefix, '..')) {
            return null;
        }

        $base = preg_replace('/\.webp$/i', '', $relativeWithoutThumbPrefix);
        if (! is_string($base) || ! preg_match('#^[A-Za-z0-9_\-]+(/[A-Za-z0-9_\-]+)*/[A-Za-z0-9_\-.]+$#', $base)) {
            return null;
        }

        $root = storage_path('app/public');
        $source = self::findSource($base);

        if ($source === null) {
            return null;
        }

        $target = "{$root}/thumbs/{$size}/{$base}.webp";
        if (is_file($target) && filemtime($target) >= filemtime($source)) {
            return $target;
        }

        try {
            $contents = @file_get_contents($source);
            $info = is_string($contents) ? @getimagesizefromstring($contents) : false;
            if (! $info || ($info[0] * $info[1]) > 40_000_000) {
                return null;
            }

            $image = @imagecreatefromstring($contents);
            if ($image === false) {
                return null;
            }

            [$width, $height] = [imagesx($image), imagesy($image)];
            $scale = min(1, $size / max($width, $height));
            $newWidth = max(1, (int) round($width * $scale));
            $newHeight = max(1, (int) round($height * $scale));

            $canvas = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
            imagecopyresampled($canvas, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            $directory = dirname($target);
            if (! is_dir($directory) && ! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
                return null;
            }

            $saved = @imagewebp($canvas, $target, 80);
            imagedestroy($canvas);
            imagedestroy($image);

            return $saved && is_file($target) ? $target : null;
        } catch (Throwable) {
            return null;
        }
    }
}
