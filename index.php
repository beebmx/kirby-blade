<?php

use Beebmx\KirbyBlade\Extensions\Snippet;
use Beebmx\KirbyBlade\Extensions\Template;
use Kirby\Cms\App as Kirby;

@include_once __DIR__.'/vendor/autoload.php';

Kirby::plugin('beebmx/kirby-blade', [
    'commands' => require_once __DIR__.'/extensions/commands.php',
    'components' => [
        'template' => new Template,
        'snippet' => new Snippet,
    ],
    'hooks' => require_once __DIR__.'/extensions/hooks.php',
    'options' => require_once __DIR__.'/extensions/options.php',
]);
