<?php

namespace Beebmx\KirbyBlade\View\Compiler;

use Beebmx\KirbyBlade\Application;
use Beebmx\KirbyBlade\View\DynamicComponent;
use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Illuminate\View\AnonymousComponent;
use Illuminate\View\Compilers\ComponentTagCompiler as TagCompiler;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

class ComponentTagCompiler extends TagCompiler
{
    /**
     * Compile the Blade component string for the given component and attributes.
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface
     */
    protected function componentString(string $component, array $attributes): string
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
                'data' => '['.$this->attributesToString($data->all(), escapeBound: false).']',
            ];

            $class = AnonymousComponent::class;
        } else {
            $parameters = $data->all();
        }

        // Dynamic components receive their attributes unescaped, so the names of the
        // bound attributes are passed along to escape them once the component is known
        $escapeAttributes = $class !== DynamicComponent::class;

        $boundAttributes = $escapeAttributes ? '' : '<?php $component->withBoundAttributes(['.$attributes->keys()
            ->filter(fn ($key) => isset($this->boundAttributes[$key]))
            ->map(fn ($key) => "'{$key}'")
            ->implode(',').']); ?>
';

        return "##BEGIN-COMPONENT-CLASS##@component('{$class}', '{$component}', [".$this->attributesToString($parameters, escapeBound: false).'])
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\\'.$class.'::ignoredParameterNames()); ?>
<?php endif; ?>
'.$boundAttributes.'<?php $component->withAttributes(['.$this->attributesToString($attributes->all(), $escapeAttributes).']); ?>';
    }

    /**
     * Get the component class for a given component alias.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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

        // Without an application namespace there are no class components to guess,
        // so the component can only be an anonymous one
        if ($this->hasApplicationNamespace()) {
            if (class_exists($class = $this->guessClassName($component))) {
                return $class;
            }

            if (class_exists($class = $class.'\\'.Str::afterLast($class, '\\'))) {
                return $class;
            }
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

    /**
     * Whether the application namespace for class components can be detected
     */
    protected function hasApplicationNamespace(): bool
    {
        try {
            Container::getInstance()->make(Application::class)->getNamespace();

            return true;
        } catch (RuntimeException) {
            return false;
        }
    }

    /**
     * Guess the class name for the given component.
     */
    public function guessClassName(string $component): string
    {
        $namespace = Container::getInstance()
            ->make(Application::class)
            ->getNamespace();

        $class = $this->formatClassName($component);

        return $namespace.'View\\Components\\'.$class;
    }
}
