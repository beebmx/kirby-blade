<?php

use Beebmx\KirbyBlade\Facades\Blade;
use Beebmx\KirbyBlade\Template;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Kirby\CLI\CLI;
use Kirby\Cms\App as Kirby;
use Kirby\Filesystem\Dir;

require_once __DIR__.'/../index.php';

/*
|--------------------------------------------------------------------------
| Hooks
|--------------------------------------------------------------------------
*/

afterEach(function () {
    Dir::remove(cachePath());
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

/**
 * Directory where Kirby (and Blade) store their cache during tests
 */
function cachePath(): string
{
    return sys_get_temp_dir().'/kirby-blade-tests';
}

/**
 * Creates a fresh Kirby instance with a fresh Blade container;
 * `$roots` and `$options` are merged with the defaults, and
 * `$props` are added to the app props (e.g. `snippets`, `templates`, `hooks`)
 */
function App(array $roots = [], array $options = [], array $props = [], string $domain = 'example.com'): Kirby
{
    // Kirby registers Whoops as error handler on every new instance
    Kirby::$enableWhoops = false;

    Template::flush();
    Container::setInstance(null);
    Facade::clearResolvedInstances();

    // Kirby keeps the session cookie of the previous instance (e.g. after
    // `@csrf`), and the new instance would wait forever for its file lock
    $_COOKIE = [];

    return new Kirby([
        'roots' => array_merge([
            'index' => fixtures(),
            'cache' => cachePath(),
            'sessions' => cachePath().'/sessions',
        ], $roots),
        'server' => [
            'SERVER_NAME' => $domain,
        ],
        'options' => array_merge([
            'debug' => true,
        ], $options),
        ...$props,
    ]);
}

/**
 * Returns the path of the given file inside the fixtures
 */
function fixtures(string $path = ''): string
{
    return rtrim(__DIR__.'/Fixtures/'.$path, '/');
}

/**
 * Creates a Kirby instance with the custom directives and if statements
 * from the starterkit, and the prefixed anonymous component path
 */
function AppWithCustomDirectives(string $environment = 'testing'): Kirby
{
    return App(
        options: [
            'beebmx.kirby-blade.ifs' => [
                'env' => fn ($expected) => $environment === $expected,
                'amiguest' => fn () => kirby()->user() === null,
            ],
            'beebmx.kirby-blade.directives' => [
                'upper' => fn ($expression) => "<?php echo strtoupper($expression); ?>",
            ],
        ],
        props: [
            'hooks' => [
                'system.loadPlugins:after' => function () {
                    Blade::anonymousComponentPath(fixtures('components'), 'dashboard');
                },
            ],
        ],
    );
}

/**
 * Renders the page with the given id and collapses its whitespace
 */
function render(Kirby $kirby, string $id): string
{
    $page = $kirby->page($id);
    $kirby->site()->visit($page);

    return trim(preg_replace('/\s+/', ' ', $page->render()));
}

/**
 * Copies the fixtures into a temporary site, for tests that write files
 */
function temporarySite(): string
{
    $index = cachePath().'/site-'.uniqid();

    Dir::copy(fixtures(), $index);

    return $index;
}

/**
 * Whether the installed Kirby version is at least the given one
 */
function isKirby(string $version): bool
{
    return version_compare(Kirby::version(), $version, '>=');
}

/**
 * A CLI double that captures the output and serves the given arguments
 */
function cli(array $args = []): CLI
{
    return new class($args) extends CLI
    {
        public array $output = [];

        public function __construct(protected array $args = []) {}

        public function arg(string $name)
        {
            return $this->args[$name] ?? null;
        }

        public function out(string $message): void
        {
            $this->output[] = $message;
        }

        public function error(string $message): void
        {
            $this->output[] = $message;
        }
    };
}
