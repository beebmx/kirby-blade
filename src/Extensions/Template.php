<?php

namespace Beebmx\KirbyBlade\Extensions;

use Beebmx\KirbyBlade\Template as BladeTemplate;
use Illuminate\Support\Str;
use Kirby\Cms\App as Kirby;
use Kirby\Http\Header;

class Template
{
    public function __invoke(Kirby $kirby, string $name, string $type = 'html', string $defaultType = 'html'): BladeTemplate
    {
        if (Str::endsWith($kirby->request()->url(), '.php')) {
            Header::redirect(substr($kirby->request()->url(), 0, -4), 301);
        }

        return new BladeTemplate($name, $type, $defaultType);
    }
}
