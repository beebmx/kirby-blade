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

//    /**
//     * Register the view finder implementation.
//     *
//     * @return void
//     */
//    public function registerViewFinder()
//    {
//        $this->app->bind('view.finder', function ($app) {
//            return new FileViewFinder($app['files'], $app['config']['view.paths']);
//        });
//    }

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

//    /**
//     * Register the Blade engine implementation.
//     *
//     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
//     * @return void
//     */
//    public function registerBladeEngine($resolver)
//    {
//        $resolver->register('blade', function () {
//            return new CompilerEngine($this->app['blade.compiler']);
//        });
//    }
}
