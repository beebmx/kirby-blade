<?php

namespace Beebmx\View\Compiler;

use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\ComponentTagCompiler as TagCompiler;
use InvalidArgumentException;

class ComponentTagCompiler extends TagCompiler
{
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
