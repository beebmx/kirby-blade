<?php

namespace Beebmx\KirbyBlade;

use Beebmx\KirbyBlade\Facades\Blade as FacadeBlade;
use Illuminate\Container\Container;
use Kirby\Cms\App;
use Kirby\Cms\App as Kirby;
use Kirby\Template\Snippet as KirbySnippet;

class Snippet extends KirbySnippet
{
    /**
     * Returns either an open snippet capturing slots
     * or the template string for self-enclosed snippets
     */
    public static function factory(
        string|array|null $name,
        array $data = [],
        bool $slots = false
    ): static|string {
        if (static::isBlade($name)) {
            FacadeBlade::viewPath(static::getPathSnippets());
            $factory = Container::getInstance()->get('view');

            return $factory->make($name, $data);
        }

        // instead of returning empty string when `$name` is null
        // allow rest of code to run, otherwise the wrong snippet would be closed
        // and potential issues for nested snippets may occur
        $file = $name !== null ? static::file($name) : null;

        // for snippets with slots, make sure to open a new
        // snippet and start capturing slots
        if ($slots === true) {
            return static::begin($file, $data);
        }

        // for snippets without slots, directly load and return
        // the snippet's template file
        return static::load($file, static::scope($data));
    }

    public static function getPathSnippets(): string
    {
        return Kirby::instance()->roots()->snippets();
    }

    public static function isBlade(string $name): bool
    {
        return file_exists(App::instance()->roots()->snippets().'/'.$name.'.'.Template::BLADE_EXTENSION);
    }
}
