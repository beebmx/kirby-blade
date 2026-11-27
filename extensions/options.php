<?php

use Kirby\Cms\App;

return [
    'app_path' => 'app',
    'bootstrap' => true,
    'directives' => [],
    'ifs' => [],
    'views' => fn (): string => App::instance()->roots()->cache().'/views',
];
