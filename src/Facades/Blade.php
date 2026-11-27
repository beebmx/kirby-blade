<?php

namespace Beebmx\KirbyBlade\Facades;

use Beebmx\KirbyBlade\Template;
use Kirby\Toolkit\Facade;

class Blade extends Facade
{
    public static function instance()
    {
        return Template::boot()->compiler();
    }
}
