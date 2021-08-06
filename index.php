<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Beebmx\Snippet;
use Illuminate\Support\Str;
use Kirby\Cms\App as Kirby;
use Kirby\Http\Header;
use Beebmx\Template;

Kirby::plugin('beebmx/kirby-blade', [
    'options' => [
        'views' => function () {
            return kirby()->roots()->cache() . '/views';
        },
        'directives' => [],
        'ifs' => [],
    ],
    'components' => [
        'template' => function (Kirby $kirby, string $name, string $contentType = null) {
            if (Str::endsWith($kirby->request()->url(), '.php')) {
                Header::redirect(substr($kirby->request()->url(), 0, -4), 301);
            }

            return new Template($kirby, $name, $contentType);
        },
        'snippet' => function (Kirby $kirby, string $name, array $data = []) {
            return (new Snippet($kirby, $name))->render($data);
        }
    ]
]);
