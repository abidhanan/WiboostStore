<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompressTextResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldCompress($request, $response)) {
            return $response;
        }

        $content = $response->getContent();

        if (! is_string($content) || strlen($content) < 1024) {
            return $response;
        }

        $encoded = gzencode($content, 6);

        if ($encoded === false) {
            return $response;
        }

        $response->setContent($encoded);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Vary', 'Accept-Encoding', false);
        $response->headers->remove('Content-Length');

        return $response;
    }

    protected function shouldCompress(Request $request, Response $response): bool
    {
        if (! extension_loaded('zlib') || ! str_contains($request->headers->get('Accept-Encoding', ''), 'gzip')) {
            return false;
        }

        if ($request->isMethod('HEAD') || $response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return false;
        }

        if (! $response->isSuccessful() || $response->headers->has('Content-Encoding')) {
            return false;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));

        return str_contains($contentType, 'text/')
            || str_contains($contentType, 'application/json')
            || str_contains($contentType, 'application/javascript')
            || str_contains($contentType, 'application/xml')
            || str_contains($contentType, 'image/svg+xml');
    }
}
