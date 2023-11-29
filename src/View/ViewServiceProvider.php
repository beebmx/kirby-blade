<?php

namespace Beebmx\View;

use Beebmx\View\Compiler\BladeCompiler;
use Illuminate\View\ViewServiceProvider as ViewProvider;

class ViewServiceProvider extends ViewProvider
{
    protected function createFactory($resolver, $finder, $events): Factory
    {
        return new Factory($resolver, $finder, $events);
    }

    /**
     * Register the Blade compiler implementation.
     */
    public function registerBladeCompiler(): void
    {
        $this->app->singleton('blade.compiler', function ($app) {
            return tap(new BladeCompiler($app['files'], $app['config']['view.compiled']), function ($blade) {
                $blade->component('dynamic-component', DynamicComponent::class);
            });
        });
    }
}
