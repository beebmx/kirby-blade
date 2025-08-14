<?php

use Beebmx\KirbyBlade\App as AppContainer;
use Beebmx\KirbyBlade\Blade;
use Beebmx\KirbyBlade\Template;
use Illuminate\Container\Container;
use Kirby\Cms\App;

return [
    'system.loadPlugins:after' => function () {
        if (App::instance()->option('beebmx.kirby-blade.bootstrap', false)) {
            $container = AppContainer::getInstance();
            Container::setInstance($container);

            new Blade(
                Template::getPathTemplates(),
                Template::getPathViews(),
                $container
            );
        }
    },
];
