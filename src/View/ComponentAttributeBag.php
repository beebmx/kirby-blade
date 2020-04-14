<?php


namespace Beebmx\View;

use Illuminate\View\ComponentAttributeBag as AttributeBag;

class ComponentAttributeBag extends AttributeBag
{

    /**
     * Merge additional attributes / values into the attribute bag.
     *
     * @param  array  $attributes
     * @return \Beebmx\View\ComponentAttributeBag
     */
    public function merge(array $attributeDefaults = [])
    {
        $attributes = [];

        $attributeDefaults = array_map(function ($value) {
            if (is_null($value) || is_bool($value)) {
                return $value;
            }

            return _e($value);
        }, $attributeDefaults);

        foreach ($this->attributes as $key => $value) {
            if ($key !== 'class') {
                $attributes[$key] = $value;

                continue;
            }

            $attributes[$key] = implode(' ', array_unique(
                array_filter([$attributeDefaults[$key] ?? '', $value])
            ));
        }

        return new static(array_merge($attributeDefaults, $attributes));
    }
}