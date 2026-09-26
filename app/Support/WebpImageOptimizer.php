<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class WebpImageOptimizer
{
    private const CONVERTIBLE_EXTENSIONS = [
        'bmp',
        'gif',
        'jpeg',
        'jpg',
        'png',
        'wbmp',
        'webp',
    ];

    public function supportsWebp(): bool
    {
        return extension_loaded('gd')
            && function_exists('imagecreatefromstring')
            && function_exists('imagewebp');
    }

    public function storeUploadedFile(
        UploadedFile $file,
        string $directory,
        string $disk = 'public',
        int $quality = 82
    ): string {
        $storedPath = $file->store($directory, $disk);

        if (! is_string($storedPath) || $storedPath === '') {
            throw new \RuntimeException('File gagal disimpan.');
        }

        $extension = strtolower(pathinfo($storedPath, PATHINFO_EXTENSION));
        if (
            ! $this->supportsWebp()
            || ! in_array($extension, self::CONVERTIBLE_EXTENSIONS, true)
            || $extension === 'webp'
        ) {
            return $storedPath;
        }

        try {
            $diskInstance = Storage::disk($disk);
            $sourcePath = $diskInstance->path($storedPath);
            $webpPath = preg_replace('/\.[^.]+$/', '.webp', $storedPath);

            if (! is_string($webpPath) || $webpPath === $storedPath) {
                return $storedPath;
            }

            $targetPath = $diskInstance->path($webpPath);
            if ($this->convert($sourcePath, $targetPath, $quality)) {
                $diskInstance->delete($storedPath);

                return $webpPath;
            }
        } catch (Throwable) {
            // File sumber tetap digunakan bila konversi tidak tersedia atau gagal.
        }

        return $storedPath;
    }

    public function createNegotiatedSibling(string $sourcePath, int $quality = 82, bool $force = false): ?string
    {
        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        if (! $this->supportsWebp() || ! in_array($extension, self::CONVERTIBLE_EXTENSIONS, true) || $extension === 'webp') {
            return null;
        }

        $targetPath = $sourcePath.'.webp';
        if (! $force && is_file($targetPath) && filemtime($targetPath) >= filemtime($sourcePath)) {
            return $targetPath;
        }

        return $this->convert($sourcePath, $targetPath, $quality) ? $targetPath : null;
    }

    public function convert(string $sourcePath, string $targetPath, int $quality = 82): bool
    {
        if (! $this->supportsWebp() || ! is_file($sourcePath) || ! is_readable($sourcePath)) {
            return false;
        }

        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        if (! in_array($extension, self::CONVERTIBLE_EXTENSIONS, true)) {
            return false;
        }

        $contents = @file_get_contents($sourcePath);
        if (! is_string($contents) || $contents === '') {
            return false;
        }

        $imageInfo = @getimagesizefromstring($contents);
        $pixelCount = is_array($imageInfo)
            ? ((int) ($imageInfo[0] ?? 0) * (int) ($imageInfo[1] ?? 0))
            : 0;

        if ($pixelCount < 1 || $pixelCount > 40_000_000) {
            return false;
        }

        $sourceImage = @imagecreatefromstring($contents);
        if ($sourceImage === false) {
            return false;
        }

        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);
        if ($width < 1 || $height < 1) {
            imagedestroy($sourceImage);

            return false;
        }

        $canvas = imagecreatetruecolor($width, $height);
        if ($canvas === false) {
            imagedestroy($sourceImage);

            return false;
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $transparent);
        imagecopy($canvas, $sourceImage, 0, 0, 0, 0, $width, $height);

        $directory = dirname($targetPath);
        if (! is_dir($directory) && ! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
            imagedestroy($canvas);
            imagedestroy($sourceImage);

            return false;
        }

        $temporaryPath = $targetPath.'.tmp-'.bin2hex(random_bytes(5));
        $written = @imagewebp($canvas, $temporaryPath, max(1, min(100, $quality)));

        imagedestroy($canvas);
        imagedestroy($sourceImage);

        if (! $written || ! is_file($temporaryPath) || filesize($temporaryPath) < 1) {
            @unlink($temporaryPath);

            return false;
        }

        if (is_file($targetPath) && ! @unlink($targetPath)) {
            @unlink($temporaryPath);

            return false;
        }

        if (! @rename($temporaryPath, $targetPath)) {
            @unlink($temporaryPath);

            return false;
        }

        @chmod($targetPath, 0644);

        return true;
    }
}
