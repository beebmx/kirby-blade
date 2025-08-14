<?php

namespace Beebmx\KirbyBlade\View;

use Beebmx\KirbyBlade\View\Compiler\BladeCompiler;
use Illuminate\View\ViewServiceProvider as ViewProvider;

class ViewServiceProvider extends ViewProvider
{
    /**
     * Register the Blade compiler implementation.
     */
    public function registerBladeCompiler(): void
    {
        $this->app->singleton('blade.compiler', fn ($app) => tap(new BladeCompiler(
            $app['files'],
            $app['config']['view.compiled'],
            $app['config']->get('view.relative_hash', false) ? $app->basePath() : '',
            $app['config']->get('view.cache', true),
            $app['config']->get('view.compiled_extension', 'php'),
        ), function ($blade) {
            $blade->component('dynamic-component', DynamicComponent::class);
        }));
    }
}
