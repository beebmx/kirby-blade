<?php

use Beebmx\Foundation\Vite;
use Illuminate\Contracts\Support\Htmlable;

if (! function_exists('b')) {
    function b($value, $doubleEncode = true): string
    {
        return _e($value, $doubleEncode);
    }
}

if (! function_exists('_e')) {
    /**
     * Encode HTML special characters in a string.
     *
     * @param  \Illuminate\Contracts\Support\Htmlable|string  $value
     * @param  bool  $doubleEncode
     */
    function _e($value, $doubleEncode = true): string
    {
        if ($value instanceof Htmlable) {
            return $value->toHtml();
        }

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }
}

if (! function_exists('public_path')) {
    function public_path($path = ''): string
    {
        return kirby()->roots()->index().($path ? DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }
}

if (! function_exists('vite')) {
    function vite($entrypoints, $buildDirectory = 'build')
    {
        return (new Vite)($entrypoints, $buildDirectory);
    }
}
