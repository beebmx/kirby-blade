<?php

namespace Beebmx\View;

use Beebmx\View\Compiler\ComponentTagCompiler;
use Closure;
use Illuminate\Container\Container;
use Illuminate\View\Compilers\BladeTagCompiler;
use Illuminate\View\DynamicComponent as Dynamic;

class DynamicComponent extends Dynamic
{
    public function render(): Closure
    {
        $template = <<<'EOF'
<?php extract(collect($attributes->getAttributes())->mapWithKeys(function ($value, $key) { return [Illuminate\Support\Str::camel(str_replace([':', '.'], ' ', $key)) => $value]; })->all(), EXTR_SKIP); ?>
{{ props }}
<x-{{ component }} {{ bindings }} {{ attributes }}>
{{ slots }}
{{ defaultSlot }}
</x-{{ component }}>
EOF;

        return function ($data) use ($template) {
            $bindings = $this->bindings($class = $this->classForComponent());

            return str_replace(
                [
                    '{{ component }}',
                    '{{ props }}',
                    '{{ bindings }}',
                    '{{ attributes }}',
                    '{{ slots }}',
                    '{{ defaultSlot }}',
                ],
                [
                    $this->component,
                    $this->compileProps($bindings),
                    $this->compileBindings($bindings),
                    class_exists($class) ? '{{ $attributes }}' : '',
                    '{{ $slot ?? "" }}',
                ],
                $template
            );
        };
    }

    protected function compiler(): ComponentTagCompiler|BladeTagCompiler
    {
        if (! static::$compiler) {
            static::$compiler = new ComponentTagCompiler(
                Container::getInstance()->get('blade.compiler')->getClassComponentAliases(),
                Container::getInstance()->get('blade.compiler')->getClassComponentNamespaces(),
                Container::getInstance()->get('blade.compiler')
            );
        }

        return static::$compiler;
    }

    protected function createBladeViewFromString($factory, $contents): string
    {
        $factory->addNamespace(
            '__components',
            $directory = Container::getInstance()->get('config')['view.compiled']
        );

        if (! is_file($viewFile = $directory.'/'.sha1($contents).'.blade.php')) {
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            file_put_contents($viewFile, $contents);
        }

        return '__components::'.basename($viewFile, '.blade.php');
    }
}
