<?php

namespace Beebmx\View\Compiler;

use Beebmx\View\AnonymousComponent;
use Beebmx\View\DynamicComponent;
use Illuminate\Container\Container;
use Illuminate\Support\Str;
use InvalidArgumentException;
use \Illuminate\View\Compilers\ComponentTagCompiler as TagCompiler;

class ComponentTagCompiler extends TagCompiler
{
    /**
     * Compile the Blade component string for the given component and attributes.
     *
     * @param  string  $component
     * @param  array  $attributes
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function componentString(string $component, array $attributes)
    {
        $class = $this->componentClass($component);

        [$data, $attributes] = $this->partitionDataAndAttributes($class, $attributes);

        $data = $data->mapWithKeys(function ($value, $key) {
            return [Str::camel($key) => $value];
        });

        // If the component doesn't exists as a class we'll assume it's a class-less
        // component and pass the component as a view parameter to the data so it
        // can be accessed within the component and we can render out the view.
        if (!class_exists($class)) {
            $parameters = [
                'view' => "'$class'",
                'data' => '[' . $this->attributesToString($data->all(), $escapeBound = false) . ']',
            ];

            $class = AnonymousComponent::class;
        } else {
            $parameters = $data->all();
        }

        return "##BEGIN-COMPONENT-CLASS##@component('{$class}', '{$component}', [" . $this->attributesToString($parameters, $escapeBound = false) . '])
<?php $component->withAttributes([' . $this->attributesToString($attributes->all(), $escapeAttributes = $class !== DynamicComponent::class) . ']); ?>';
    }

    /**
     * Get the component class for a given component alias.
     *
     * @param  string  $component
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function componentClass(string $component)
    {
        $viewFactory = Container::getInstance()->get('view');

        if (isset($this->aliases[$component])) {
            if (class_exists($alias = $this->aliases[$component])) {
                return $alias;
            }

            if ($viewFactory->exists($alias)) {
                return $alias;
            }

            throw new InvalidArgumentException(
                "Unable to locate class or view [{$alias}] for component [{$component}]."
            );
        }

        $guess = collect($this->blade->getAnonymousComponentNamespaces())
            ->filter(function ($directory, $prefix) use ($component) {
                return Str::startsWith($component, $prefix . '::');
            })
            ->prepend('components', $component)
            ->reduce(function ($carry, $directory, $prefix) use ($component, $viewFactory) {
                if (!is_null($carry)) {
                    return $carry;
                }

                $componentName = Str::after($component, $prefix . '::');

                if ($viewFactory->exists($view = $this->guessViewName($componentName, $directory))) {
                    return $view;
                }

                if ($viewFactory->exists($view = $this->guessViewName($componentName, $directory) . '.index')) {
                    return $view;
                }
            });

        if (!is_null($guess)) {
            return $guess;
        }

        throw new InvalidArgumentException(
            "Unable to locate a class or view for component [{$component}]."
        );
    }

    /**
     * Convert an array of attributes to a string.
     *
     * @param  array  $attributes
     * @param  bool  $escapeBound
     * @return string
     */
    protected function attributesToString(array $attributes, $escapeBound = true)
    {
        return collect($attributes)
            ->map(function (string $value, string $attribute) use ($escapeBound) {
                return $escapeBound && isset($this->boundAttributes[$attribute]) && $value !== 'true' && !is_numeric($value)
                    ? "'{$attribute}' => \Beebmx\View\Compiler\BladeCompiler::sanitizeComponentAttribute({$value})"
                    : "'{$attribute}' => {$value}";
            })
            ->implode(',');
    }
}
