<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Memadatkan blok <style> inline pada respons HTML (hapus komentar & spasi berlebih) supaya dokumen
 * lebih kecil. Hanya menyentuh isi <style>; script, markup, dan CSS eksternal tidak diubah.
 */
class MinifyInlineCss
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            $request->isMethod('HEAD')
            || $response instanceof BinaryFileResponse
            || $response instanceof StreamedResponse
            || ! $response->isSuccessful()
            || $response->headers->has('Content-Encoding')
            || ! str_contains(strtolower((string) $response->headers->get('Content-Type', '')), 'text/html')
        ) {
            return $response;
        }

        $content = $response->getContent();

        if (! is_string($content) || ! str_contains($content, '<style')) {
            return $response;
        }

        $minified = preg_replace_callback(
            '#(<style\b[^>]*>)(.*?)(</style>)#is',
            fn (array $match) => $match[1].self::minify($match[2]).$match[3],
            $content
        );

        if (is_string($minified) && $minified !== '') {
            $response->setContent($minified);
        }

        return $response;
    }

    public static function minify(string $css): string
    {
        // Lewati blok yang berisi template/placeholder atau string panjang bersepasi khusus.
        if (str_contains($css, '<?') || str_contains($css, '{{')) {
            return $css;
        }

        $css = preg_replace('#/\*.*?\*/#s', '', $css) ?? $css;

        // Lindungi string dan url(...) (mis. data URI SVG) agar spasi/koma di dalamnya tidak berubah.
        $protected = [];
        $css = preg_replace_callback(
            '/"(?:[^"\\\\]|\\\\.)*"|\'(?:[^\'\\\\]|\\\\.)*\'|url\([^)]*\)/s',
            function (array $match) use (&$protected): string {
                $protected[] = $match[0];

                return "\x01".(count($protected) - 1)."\x02";
            },
            $css
        ) ?? $css;

        $css = preg_replace('/\s+/', ' ', $css) ?? $css;
        $css = preg_replace('/\s*([{};,>])\s*/', '$1', $css) ?? $css;
        $css = str_replace(';}', '}', $css);

        return trim((string) preg_replace_callback(
            '/\x01(\d+)\x02/',
            fn (array $match): string => $protected[(int) $match[1]] ?? '',
            $css
        ));
    }
}
