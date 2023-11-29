<?php

namespace Beebmx\KirbyBlade;

use Beebmx\Blade\Blade as BladeProvider;
use Beebmx\View\ViewServiceProvider;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use Illuminate\Contracts\View\View;
use Illuminate\View\Compilers\BladeCompiler;

class Blade extends BladeProvider
{
    protected $container;

    private $factory;

    private $compiler;

    public function __construct($viewPaths, string $cachePath, ContainerInterface $container = null)
    {
        $this->container = $container ?: new Container;
        $this->setupContainer((array) $viewPaths, $cachePath);

        (new ViewServiceProvider($this->container))->register();
        Container::setInstance($this->container);

        $this->factory = $this->container->get('view');
        $this->compiler = $this->container->get('blade.compiler');
    }

    public function make($view, $data = [], $mergeData = []): View
    {
        return $this->factory->make($view, $data, $mergeData);
    }

    public function compiler(): BladeCompiler
    {
        return $this->compiler;
    }
}
