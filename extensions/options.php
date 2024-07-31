<?php

use Kirby\Cms\App;

return [
    'bootstrap' => false,
    'views' => function (): string {
        return App::instance()->roots()->cache().'/views';
    },
    'directives' => [],
    'ifs' => [],
];
