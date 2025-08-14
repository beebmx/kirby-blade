<?php

use Kirby\Cms\App;

return [
    'bootstrap' => false,
    'views' => fn (): string => App::instance()->roots()->cache().'/views',
    'directives' => [],
    'ifs' => [],
];
