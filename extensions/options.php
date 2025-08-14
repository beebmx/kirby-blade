<?php

use Kirby\Cms\App;

return [
    'app_path' => 'app',
    'bootstrap' => false,
    'directives' => [],
    'ifs' => [],
    'views' => fn (): string => App::instance()->roots()->cache().'/views',
];
