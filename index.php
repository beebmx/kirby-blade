<?php

use Beebmx\Snippet;
use Beebmx\Template;
use Illuminate\Support\Str;
use Kirby\Cms\App as Kirby;
use Kirby\Http\Header;
use Kirby\Template\Snippet as KirbySnippet;

@include_once __DIR__.'/vendor/autoload.php';

Kirby::plugin('beebmx/kirby-blade', [
    'options' => require_once __DIR__.'/extensions/options.php',
    'hooks' => require_once __DIR__.'/extensions/hooks.php',
    'components' => [
        'template' => function (Kirby $kirby, string $name, ?string $contentType = null) {
            if (Str::endsWith($kirby->request()->url(), '.php')) {
                Header::redirect(substr($kirby->request()->url(), 0, -4), 301);
            }

            return new Template($kirby, $name, $contentType);
        },
        'snippet' => function (Kirby $kirby, string $name, array $data = [], bool $slots = false): KirbySnippet|string {
            return Snippet::factory($name, $data, $slots);
        },
    ],
]);
