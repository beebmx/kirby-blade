<?php

use Kirby\Cms\App;

return [
    'views' => function (): string {
        return App::instance()->roots()->cache().'/views';
    },
    'directives' => [],
    'ifs' => [],
];
