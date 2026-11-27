<?php

namespace Beebmx\KirbyBlade\View;

use Beebmx\KirbyBlade\View\Compiler\BladeCompiler;
use Beebmx\KirbyBlade\View\Compiler\ComponentTagCompiler;
use Illuminate\Container\Container;
use Illuminate\View\DynamicComponent as Dynamic;

class DynamicComponent extends Dynamic
{
    /**
     * The names of the attributes that were bound on the component tag.
     *
     * @var array
     */
    protected $boundAttributes = [];

    /**
     * The methods that should not be exposed to the component view.
     *
     * @var array
     */
    protected $except = ['withBoundAttributes'];

    /**
     * Set the names of the attributes that were bound on the component tag.
     */
    public function withBoundAttributes(array $attributes): static
    {
        $this->boundAttributes = $attributes;

        return $this;
    }

    /**
     * Set the extra attributes that the component should make available;
     * bound attributes that are not component data are escaped, unless the
     * installed illuminate/view version already escapes them itself
     *
     * @return $this
     */
    public function withAttributes(array $attributes)
    {
        if (method_exists(Dynamic::class, 'withBoundAttributes')) {
            return parent::withAttributes($attributes);
        }

        if ($this->boundAttributes && class_exists($class = $this->classForComponent())) {
            [$data] = $this->compiler()->partitionDataAndAttributes($class, $attributes);

            foreach ($this->boundAttributes as $key) {
                if (array_key_exists($key, $attributes) && ! $data->has($key)) {
                    $attributes[$key] = BladeCompiler::sanitizeComponentAttribute($attributes[$key]);
                }
            }
        }

        return parent::withAttributes($attributes);
    }

    protected function compiler(): ComponentTagCompiler
    {
        if (! static::$compiler) {
            static::$compiler = new ComponentTagCompiler(
                Container::getInstance()->make('blade.compiler')->getClassComponentAliases(),
                Container::getInstance()->make('blade.compiler')->getClassComponentNamespaces(),
                Container::getInstance()->make('blade.compiler')
            );
        }

        return static::$compiler;
    }
}
