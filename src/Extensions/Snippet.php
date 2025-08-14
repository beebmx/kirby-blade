<?php

namespace Beebmx\KirbyBlade\Extensions;

use Beebmx\KirbyBlade\Snippet as BladeSnippet;
use Kirby\Cms\App as Kirby;
use Kirby\Template\Snippet as KirbySnippet;

class Snippet
{
    public function __invoke(Kirby $kirby, string $name, array $data = [], bool $slots = false): KirbySnippet|string
    {
        return BladeSnippet::factory($name, $data, $slots);
    }
}
