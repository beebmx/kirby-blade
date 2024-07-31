<?php

use Beebmx\Blade\Container as BladeContainer;
use Beebmx\KirbyBlade\Blade;
use Beebmx\Template;
use Illuminate\Container\Container;

return [
    'system.loadPlugins:after' => function () {
        $container = new BladeContainer;
        Container::setInstance($container);

        new Blade(
            Template::getPathTemplates(),
            Template::getPathViews(),
            $container
        );
    },
];
