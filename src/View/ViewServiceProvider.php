<?php

namespace Beebmx\View;

use Illuminate\View\ViewServiceProvider as ViewProvider;
use Illuminate\View\Engines\CompilerEngine;
use Beebmx\View\Compiler\BladeCompiler;

class ViewServiceProvider extends ViewProvider
{
    protected function createFactory($resolver, $finder, $events)
    {
        return new Factory($resolver, $finder, $events);
    }

    /**
     * Register the Blade compiler implementation.
     *
     * @return void
     */
    public function registerBladeCompiler()
    {
        $this->app->singleton('blade.compiler', function ($app) {
            return new BladeCompiler(
                $app['files'],
                $app['config']['view.compiled']
            );
        });
    }
}
