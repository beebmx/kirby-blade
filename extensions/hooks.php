<?php

use Beebmx\KirbyBlade\Template;
use Kirby\Cms\App;

return [
    'system.loadPlugins:after' => function () {
        if (App::instance()->option('beebmx.kirby-blade.bootstrap', true)) {
            Template::boot();
        }
    },
];
