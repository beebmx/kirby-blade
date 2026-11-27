<?php

namespace Beebmx\KirbyBlade;

use Kirby\Cms\App as Kirby;
use Kirby\Filesystem\F;
use Kirby\Template\Slots;
use Kirby\Template\Snippet as KirbySnippet;

class Snippet extends KirbySnippet
{
    /**
     * Absolute path to the snippet file; Blade snippets are found
     * before the regular snippets with the same name
     */
    public static function file(string|array $name): ?string
    {
        return static::getBladeFile($name) ?? parent::file($name);
    }

    /**
     * Renders the snippet file; Blade snippets are rendered with Blade
     * and receive the same data as regular snippets, `$slot` and `$slots` included
     */
    public static function load(?string $file = null, array $data = [], ?Slots $slots = null): string
    {
        if (! str_ends_with($file ?? '', '.'.Template::BLADE_EXTENSION)) {
            return parent::load($file, $data, $slots);
        }

        // Kirby 4 and 5 scope the data before loading the snippet; Kirby 6 scopes it while loading
        if (! (($data['slots'] ?? null) instanceof Slots)) {
            $data = static::scope($data, $slots);
        }

        return Template::boot()->file($file, $data)->render();
    }

    public static function getPathSnippets(): string
    {
        return Kirby::instance()->roots()->snippets();
    }

    public static function getBladeFile(string|array|null $name): ?string
    {
        $root = static::getPathSnippets();

        foreach ((array) $name as $snippet) {
            $snippet = (string) $snippet;

            if (F::exists($file = $root.'/'.$snippet.'.'.Template::BLADE_EXTENSION, $root)) {
                return $file;
            }

            if (F::exists($root.'/'.$snippet.'.php', $root)) {
                return null;
            }

            if ($file = Kirby::instance()->extension('snippets', $snippet)) {
                return str_ends_with($file, '.'.Template::BLADE_EXTENSION) ? $file : null;
            }
        }

        return null;
    }

    public static function isBlade(string|array|null $name): bool
    {
        return static::getBladeFile($name) !== null;
    }
}
