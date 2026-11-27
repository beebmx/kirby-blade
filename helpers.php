<?php

if (! defined('KIRBY_HELPER_E')) {
    define('KIRBY_HELPER_E', false);
}

if (! defined('KIRBY_HELPER_DUMP')) {
    define('KIRBY_HELPER_DUMP', false);
}

if (! defined('KIRBY_HELPER_GO')) {
    define('KIRBY_HELPER_GO', false);
}

use Beebmx\KirbyBlade\Foundation\Vite;
use Beebmx\KirbyBlade\Template;
use Illuminate\Contracts\Support\DeferringDisplayableValue;
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
    function _e(DeferringDisplayableValue|Htmlable|BackedEnum|Stringable|string|int|float|bool|null $value, bool $doubleEncode = true): string
    {
        if ($value instanceof DeferringDisplayableValue) {
            $value = $value->resolveDisplayableValue();
        }

        if ($value instanceof Htmlable) {
            return $value->toHtml() ?? '';
        }

        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);
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
        return join_paths(App::instance()->roots()->base() ?? App::instance()->roots()->index(), $path);
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
        $factory = Template::boot()->container()->get('view');

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}
