<?php

use Beebmx\KirbyBlade\Foundation\Vite;
use Illuminate\Container\Container;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Kirby\Cms\App;

if (! function_exists('b')) {
    function b($value, $doubleEncode = true): string
    {
        return _e($value, $doubleEncode);
    }
}

if (! function_exists('_e')) {
    /**
     * Encode HTML special characters in a string.
     */
    function _e(Htmlable|string $value, bool $doubleEncode = true): string
    {
        if ($value instanceof Htmlable) {
            return $value->toHtml();
        }

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }
}

if (! function_exists('join_paths')) {
    function join_paths($basePath, ...$paths): string
    {
        foreach ($paths as $index => $path) {
            if (empty($path) && $path !== '0') {
                unset($paths[$index]);
            } else {
                $paths[$index] = DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR);
            }
        }

        return $basePath.implode('', $paths);
    }
}

if (! function_exists('public_path')) {
    function public_path($path = ''): string
    {
        return join_paths(App::instance()->roots()->index(), $path);
    }
}

if (! function_exists('base_path')) {
    function base_path($path = ''): string
    {
        return join_paths(App::instance()->roots()->base(), $path);
    }
}

if (! function_exists('app_path')) {
    function app_path($path = ''): string
    {
        return join_paths(base_path(App::instance()->option('beebmx.kirby-blade.app_path', 'app')), $path);
    }
}

if (! function_exists('vite')) {
    /**
     * @throws Exception
     */
    function vite($entrypoints, $buildDirectory = 'build'): HtmlString
    {
        return (new Vite)($entrypoints, $buildDirectory);
    }
}

if (! function_exists('view')) {
    function view($view = null, $data = [], $mergeData = [])
    {
        $factory = Container::getInstance()->get('view');

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}
