<?php

namespace Beebmx\KirbyBlade\View;

use Beebmx\KirbyBlade\View\Compiler\ComponentTagCompiler;
use Illuminate\Container\Container;
use Illuminate\View\DynamicComponent as Dynamic;

class DynamicComponent extends Dynamic
{
    protected function compiler(): ComponentTagCompiler
    {
        if (! static::$compiler) {
            static::$compiler = new ComponentTagCompiler(
                Container::getInstance()->make('blade.compiler')->getClassComponentAliases(),
                Container::getInstance()->make('blade.compiler')->getClassComponentNamespaces(),
                Container::getInstance()->make('blade.compiler')
            );
        }

        return static::$compiler;
    }
}
