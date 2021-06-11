<?php

namespace Beebmx\View;

use Illuminate\View\AnonymousComponent as Anonymous;

class AnonymousComponent extends Anonymous
{
    /**
     * Get the data that should be supplied to the view.
     *
     * @return array
     */
    public function data()
    {
        $this->attributes = $this->attributes ?: new ComponentAttributeBag;

        return $this->data + ['attributes' => $this->attributes];
    }
}
