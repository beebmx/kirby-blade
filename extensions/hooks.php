<?php

use Beebmx\Blade\Container as BladeContainer;
use Beebmx\KirbyBlade\Blade;
use Beebmx\Template;
use Illuminate\Container\Container;
use Kirby\Cms\App;

return [
    'system.loadPlugins:after' => function () {
        if(App::instance()->option('beebmx.kirby-blade.bootstrap', false)) {
            $container = new BladeContainer;
            Container::setInstance($container);

            new Blade(
                Template::getPathTemplates(),
                Template::getPathViews(),
                $container
            );
        }
    },
];
