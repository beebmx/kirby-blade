<?php

namespace Beebmx\View\Compiler;

use Illuminate\View\Compilers\BladeCompiler as Compiler;

class BladeCompiler extends Compiler
{
    /**
     * The "regular" / legacy echo string format.
     *
     * @var string
     */
    protected $echoFormat = '_e(%s)';

    /**
     * Register an "if" statement directive.
     *
     * @param  string  $name
     */
    public function if($name, callable $callback): void
    {
        $this->conditions[$name] = $callback;
        $this->directive($name, function ($expression) use ($name) {
            return $expression !== ''
                    ? "<?php if (option('beebmx.kirby-blade.ifs')['{$name}']($expression)): ?>"
                    : "<?php if (option('beebmx.kirby-blade.ifs')['{$name}'](null)): ?>";
        });
        $this->directive('else'.$name, function ($expression) use ($name) {
            return $expression !== ''
                ? "<?php elseif (option('beebmx.kirby-blade.ifs')['{$name}']($expression)): ?>"
                : "<?php elseif (option('beebmx.kirby-blade.ifs')['{$name}'](null)): ?>";
        });
        $this->directive('end'.$name, function () {
            return '<?php endif; ?>';
        });
    }

    /**
     * Set the "echo" format to double encode entities.
     */
    public function withDoubleEncoding(): void
    {
        $this->setEchoFormat('_e(%s, true)');
    }

    /**
     * Set the "echo" format to not double encode entities.
     */
    public function withoutDoubleEncoding(): void
    {
        $this->setEchoFormat('_e(%s, false)');
    }

    /**
     * Compile the component tags.
     *
     * @param  string  $value
     */
    protected function compileComponentTags($value): string
    {
        if (! $this->compilesComponentTags) {
            return $value;
        }

        return (new ComponentTagCompiler(
            $this->classComponentAliases,
            $this->classComponentNamespaces,
            $this
        ))->compile($value);
    }

    /**
     * Sanitize the given component attribute value.
     *
     * @param  mixed  $value
     */
    public static function sanitizeComponentAttribute($value): mixed
    {
        return is_string($value) ||
        (is_object($value) && method_exists($value, '__toString'))
            ? _e($value)
            : $value;
    }
}
