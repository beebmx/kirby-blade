<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Profile extends Component
{
    public function __construct(public string $name = 'Guest') {}

    public function render(): View
    {
        return view('components.profile');
    }
}
