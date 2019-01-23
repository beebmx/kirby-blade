<?php

use Kirby\Cms\App as Kirby;
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
            return new Template($kirby, $name, $contentType);
        }
    ]
]);
