<?php

namespace Beebmx\KirbyBlade\View\Compiler;

use Illuminate\Container\Container;
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

    public function anonymousComponentPath(string $path, ?string $prefix = null): void
    {
        $prefixHash = hash('xxh128', $prefix ?: $path);

        $this->anonymousComponentPaths[] = [
            'path' => $path,
            'prefix' => $prefix,
            'prefixHash' => $prefixHash,
        ];

        Container::getInstance()
            ->get('view')
            ->addNamespace($prefixHash, $path);
    }

    public function replaceAnonymousComponentPath(string $path, ?string $prefix = null): void
    {
        $prefixHash = hash('xxh128', $prefix ?: $path);

        Container::getInstance()
            ->get('view')
            ->replaceNamespace($prefixHash, $path);
    }

    /**
     * Register a new view path.
     */
    public function viewPath(string $path): void
    {
        Container::getInstance()
            ->get('view')
            ->addLocation($path);
    }
}
