<?php

namespace Beebmx;

use Beebmx\Blade\Container;
use Beebmx\KirbyBlade\Blade;
use Kirby\Cms\App as Kirby;
use Kirby\Toolkit\Tpl;

class Snippet extends Template
{
    protected $snippet;

    public function __construct(Kirby $kirby, string $name, string $type = 'html', string $defaultType = 'html')
    {
        $this->template = $kirby->roots()->snippets();
        $this->views = $this->getPathViews();
        $this->snippet = $this->template.'/'.$name.'.php';

        $blade = $this->template.'/'.$name.'.'.$this->bladeExtension();

        if (file_exists($this->snippet) === false && file_exists($blade) === false) {
            $this->snippet = $kirby->extensions('snippets')[$name];
        }

        $this->name = strtolower($name);
        $this->type = $type;
        $this->defaultType = $defaultType;

        $this->setViewDirectory();
    }

    public function render(array $data = []): string
    {
        if ($this->isBlade()) {
            $this->blade = new Blade(
                $this->template,
                $this->views,
                new Container
            );
            $this->setDirectives();
            $this->setIfStatements();

            return $this->blade->make($this->name, $data);
        } else {
            return Tpl::load($this->snippet, $data);
        }
    }
}
