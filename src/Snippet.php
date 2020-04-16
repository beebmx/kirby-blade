<?php

namespace Beebmx;

use Kirby\Cms\App as Kirby;

class Snippet extends Template
{
    public function __construct(Kirby $kirby, string $name, string $type = 'html', string $defaultType = 'html')
    {
        $this->template = $kirby->roots()->snippets();
        $this->views = $this->getPathViews();

        $this->name = strtolower($name);
        $this->type = $type;
        $this->defaultType = $defaultType;

        $this->setViewDirectory();
    }
}
