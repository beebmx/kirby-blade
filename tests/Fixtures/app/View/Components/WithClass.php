<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class WithClass extends Component
{
    public function __construct(public string $message = 'Hello, World!') {}

    public function render(): View
    {
        return view('components.with-class');
    }
}
