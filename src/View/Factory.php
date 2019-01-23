<?php

namespace Beebmx\View;

use Illuminate\View\Factory as FactoryView;
use Beebmx\View\Concerns\ManagesLayouts;

class Factory extends FactoryView
{
    use ManagesLayouts;
}
