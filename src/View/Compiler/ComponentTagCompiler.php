<?php

namespace Beebmx\View\Compiler;


use Beebmx\View\DynamicComponent;
use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Illuminate\View\AnonymousComponent;
use Illuminate\View\Compilers\ComponentTagCompiler as TagCompiler;
use InvalidArgumentException;

class ComponentTagCompiler extends TagCompiler
{
    /**
     * Compile the Blade component string for the given component and attributes.
     *
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

        // If the component doesn't exist as a class, we'll assume it's a class-less
        // component and pass the component as a view parameter to the data so it
        // can be accessed within the component and we can render out the view.
        if (! class_exists($class)) {
            $view = Str::startsWith($component, 'mail::')
                ? "\$__env->getContainer()->make(Illuminate\\View\\Factory::class)->make('{$component}')"
                : "'$class'";

            $parameters = [
                'view' => $view,
                'data' => '['.$this->attributesToString($data->all(), $escapeBound = false).']',
            ];

            $class = AnonymousComponent::class;
        } else {
            $parameters = $data->all();
        }

        return "##BEGIN-COMPONENT-CLASS##@component('{$class}', '{$component}', [".$this->attributesToString($parameters, $escapeBound = false).'])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\\'.$class.'::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['.$this->attributesToString($attributes->all(), $escapeAttributes = $class !== DynamicComponent::class).']); ?>';
    }

    /**
     * Get the component class for a given component alias.
     *
     *
     * @throws \InvalidArgumentException
     */
    public function componentClass(string $component): string
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

        if ($class = $this->findClassByComponent($component)) {
            return $class;
        }

        if (! is_null($guess = $this->guessAnonymousComponentUsingNamespaces($viewFactory, $component)) ||
            ! is_null($guess = $this->guessAnonymousComponentUsingPaths($viewFactory, $component))) {
            return $guess;
        }

        if (Str::startsWith($component, 'mail::')) {
            return $component;
        }

        throw new InvalidArgumentException(
            "Unable to locate a class or view for component [{$component}]."
        );
    }
}
