<?php

namespace Beebmx\View;

use Illuminate\View\ViewServiceProvider as ViewProvider;
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
            return tap(new BladeCompiler($app['files'], $app['config']['view.compiled']), function ($blade) {
                $blade->component('dynamic-component', DynamicComponent::class);
            });
        });
    }
}
