<?php

use Beebmx\KirbyBlade\Console\MakeComponentCommand;
use Beebmx\KirbyBlade\Console\ViewClearCommand;

return [
    'view:clear' => [
        'description' => 'Clear all compiled view file',
        'command' => new ViewClearCommand,
    ],
    'make:component' => [
        'description' => 'Create a new view component class',
        'args' => [
            'name' => [
                'description' => 'The name of the component',
                'required' => true,
            ],
            'path' => [
                'description' => 'The location where the component view should be created',
                'longPrefix' => 'path',
            ],
            'view' => [
                'description' => 'Create an anonymous component with only a view',
                'longPrefix' => 'view',
                'noValue' => true,
            ],
        ],
        'command' => new MakeComponentCommand,
    ],
];
