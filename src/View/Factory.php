<?php

namespace Beebmx\View;

use Beebmx\View\Concerns\ManagesLayouts;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\HtmlString;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory as FactoryView;
use Illuminate\View\FileViewFinder;

class Factory extends FactoryView
{
    use ManagesLayouts;

    /**
     * Create a new view factory instance.
     *
     * @param  \Illuminate\View\ViewFinderInterface  $finder
     * @return void
     */
    public function __construct(EngineResolver $engines, FileViewFinder $finder, ?Dispatcher $events = null)
    {
        $this->finder = $finder;
        $this->events = $events;
        $this->engines = $engines;

        $this->share('__env', $this);
    }

    /**
     * Get the data for the given component.
     */
    protected function componentData(): array
    {
        $defaultSlot = new HtmlString(trim(ob_get_clean()));

        $slots = array_merge([
            '__default' => $defaultSlot,
        ], $this->slots[count($this->componentStack)]);

        return array_merge(
            $this->componentData[count($this->componentStack)],
            ['slot' => $defaultSlot],
            $this->slots[count($this->componentStack)],
        );
    }
}
