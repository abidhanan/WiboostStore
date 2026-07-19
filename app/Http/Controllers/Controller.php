<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

abstract class Controller
{
    protected function redirectToReturnTo(Request $request, string $fallbackRoute, array $fallbackParameters = [])
    {
        $returnTo = trim((string) $request->input('return_to', ''));

        if ($returnTo !== '') {
            $appUrl = rtrim((string) config('app.url'), '/');
            $currentRoot = rtrim($request->getSchemeAndHttpHost(), '/');

            if (Str::startsWith($returnTo, ['/admin/', $appUrl . '/admin/', $currentRoot . '/admin/'])) {
                return redirect()->to($returnTo);
            }
        }

        return redirect()->route($fallbackRoute, $fallbackParameters);
    }
}
