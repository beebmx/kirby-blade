<?php

namespace Beebmx\KirbyBlade\Console;

use Beebmx\KirbyBlade\Application;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Kirby\CLI\CLI;
use Kirby\Cms\App as Kirby;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

class MakeComponentCommand
{
    protected array $reservedNames = [
        '__halt_compiler',
        'abstract',
        'and',
        'array',
        'as',
        'break',
        'callable',
        'case',
        'catch',
        'class',
        'clone',
        'const',
        'continue',
        'declare',
        'default',
        'die',
        'do',
        'echo',
        'else',
        'elseif',
        'empty',
        'enddeclare',
        'endfor',
        'endforeach',
        'endif',
        'endswitch',
        'endwhile',
        'enum',
        'eval',
        'exit',
        'extends',
        'false',
        'final',
        'finally',
        'fn',
        'for',
        'foreach',
        'function',
        'global',
        'goto',
        'if',
        'implements',
        'include',
        'include_once',
        'instanceof',
        'insteadof',
        'interface',
        'isset',
        'list',
        'match',
        'namespace',
        'new',
        'or',
        'parent',
        'print',
        'private',
        'protected',
        'public',
        'readonly',
        'require',
        'require_once',
        'return',
        'self',
        'static',
        'switch',
        'throw',
        'trait',
        'true',
        'try',
        'unset',
        'use',
        'var',
        'while',
        'xor',
        'yield',
        '__CLASS__',
        '__DIR__',
        '__FILE__',
        '__FUNCTION__',
        '__LINE__',
        '__METHOD__',
        '__NAMESPACE__',
        '__TRAIT__',
    ];

    /**
     * @throws Exception
     */
    public function __invoke(CLI $cli): void
    {
        if ($cli->arg('view')) {
            $this->writeView($cli);

            return;
        }

        $this->writeClass($cli);
        $this->writeView($cli);
    }

    protected function writeView(CLI $cli): void
    {
        $component = $this->getView($cli);
        $path = $this->viewPath(
            str_replace('.', '/', $component).'.blade.php'
        );

        if (! Dir::exists(dirname($path))) {
            Dir::make(dirname($path));
        }

        if (F::exists($path) && ! $cli->arg('force')) {
            $cli->error('View already exists.');

            return;
        }

        file_put_contents(
            $path,
            '<div>
    {{-- '.$component.'.blade.php  --}}
</div>');

        $path = str_replace(Kirby::instance()->roots()->templates(), '', $path);

        $cli->out(sprintf('%s [%s] created successfully.', 'View', $path));
    }

    /**
     * @throws Exception
     */
    protected function writeClass(CLI $cli): void
    {
        if ($this->isReservedName($this->getNameInput($cli))) {
            $cli->error('The name "'.$this->getNameInput($cli).'" is reserved by PHP.');

            return;
        }

        $name = $this->qualifyClass($this->getNameInput($cli));

        $path = $this->getPath($name);

        if (! $cli->arg('force') &&
            $this->alreadyExists($this->getNameInput($cli))) {
            $cli->error('Component already exists.');

            return;
        }

        $this->makeDirectory($path);

        $cli->make($path, $this->sortImports($this->buildClass($name, $cli)));

        $path = str_replace(Kirby::instance()->roots()->base(), '', $path);

        $cli->out(sprintf('%s [%s] created successfully.', 'Component', $path));
    }

    protected function getView(CLI $cli): string
    {
        $segments = explode('/', str_replace('\\', '/', $cli->arg('name')));

        $name = array_pop($segments);

        $path = is_string($cli->arg('path') ?? null) && ! empty($cli->arg('path'))
            ? explode('/', trim($cli->arg('path'), '/'))
            : [
                'components',
                ...$segments,
            ];

        $path[] = $name;

        return (new Collection($path))
            ->map(fn ($segment) => Str::kebab($segment))
            ->implode('.');
    }

    protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return app_path().'/'.str_replace('\\', '/', $name).'.php';
    }

    protected function viewPath(string $path = ''): string
    {
        $views = rtrim(Container::getInstance()['config']['view.paths'][0], DIRECTORY_SEPARATOR);

        return $views.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    protected function isReservedName($name): bool
    {
        return in_array(
            strtolower($name),
            (new Collection($this->reservedNames))
                ->transform(fn ($name) => strtolower($name))
                ->all()
        );
    }

    protected function getNameInput(CLI $cli): string
    {
        $name = trim($cli->arg('name'));

        if (Str::endsWith($name, '.php')) {
            return Str::substr($name, 0, -4);
        }

        return $name;
    }

    protected function rootNamespace(): string
    {
        return Application::getInstance()
            ->getNamespace();
    }

    protected function qualifyClass($name): array|string
    {
        $name = ltrim($name, '\\/');

        $name = str_replace('/', '\\', $name);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name
        );
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\View\Components';
    }

    protected function alreadyExists(string $rawName): bool
    {
        return F::exists($this->getPath($this->qualifyClass($rawName)));
    }

    /**
     * @throws Exception
     */
    protected function makeDirectory($path)
    {
        if (! Dir::exists(dirname($path))) {
            Dir::make(dirname($path));
        }

        return $path;
    }

    protected function buildClass(string $name, CLI $cli): string
    {
        $stub = F::read($this->getStub());

        return str_replace(
            ['DummyView', '{{ view }}'],
            'view(\''.$this->getView($cli).'\')',
            $this->replaceNamespace($stub, $name)->replaceClass($stub, $name)
        );
    }

    protected function getStub(): string
    {
        return __DIR__.'/stubs/component.stub';
    }

    protected function replaceNamespace(&$stub, $name): static
    {
        $searches = [
            ['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel'],
            ['{{ namespace }}', '{{ rootNamespace }}', '{{ namespacedUserModel }}'],
            ['{{namespace}}', '{{rootNamespace}}', '{{namespacedUserModel}}'],
        ];

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$this->getNamespace($name), $this->rootNamespace()],
                $stub
            );
        }

        return $this;
    }

    protected function replaceClass($stub, $name): array|string
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace(['DummyClass', '{{ class }}', '{{class}}'], $class, $stub);
    }

    protected function getNamespace($name): string
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    protected function sortImports($stub)
    {
        if (preg_match('/(?P<imports>(?:^use [^;{]+;$\n?)+)/m', $stub, $match)) {
            $imports = explode("\n", trim($match['imports']));

            sort($imports);

            return str_replace(trim($match['imports']), implode("\n", $imports), $stub);
        }

        return $stub;
    }
}
