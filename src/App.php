<?php

namespace Beebmx\KirbyBlade;

use Beebmx\Blade\Container;

class App extends Container
{
    public function getNamespace(): string
    {
        return 'App\\';
    }
}
