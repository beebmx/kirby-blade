<?php

namespace Beebmx\View;

use Illuminate\View\FileViewFinder;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory as FactoryView;
use Beebmx\View\Concerns\ManagesLayouts;

class Factory extends FactoryView
{
    use ManagesLayouts;

    /**
     * Create a new view factory instance.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $engines
     * @param  \Illuminate\View\ViewFinderInterface  $finder
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function __construct(EngineResolver $engines, FileViewFinder $finder, Dispatcher $events = null)
    {
        $this->finder = $finder;
        $this->events = $events;
        $this->engines = $engines;

        $this->share('__env', $this);
    }
}
