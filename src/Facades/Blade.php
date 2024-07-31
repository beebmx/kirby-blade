<?php

namespace Beebmx\Facades;

use Illuminate\Container\Container;
use Kirby\Toolkit\Facade;

class Blade extends Facade
{
    public static function instance()
    {
        return Container::getInstance()->get('blade.compiler');
    }
}
