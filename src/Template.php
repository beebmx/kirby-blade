<?php

namespace Beebmx\KirbyBlade;

use Exception;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use Kirby\Cms\App as Kirby;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Template\Template as KirbyTemplate;

class Template extends KirbyTemplate
{
    public const string BLADE_EXTENSION = 'blade.php';

    protected Blade $blade;

    /**
     * Resolved template file, cached since Kirby asks for it several times
     */
    protected ?string $file = null;

    protected bool $resolved = false;

    protected static ?Blade $booted = null;

    /**
     * Detects the location of the template file
     * if it exists.
     */
    public function file(): ?string
    {
        if ($this->resolved === false) {
            $this->file = $this->resolveFile();
            $this->resolved = true;
        }

        return $this->file;
    }

    /**
     * Looks for the template file in the templates directory
     * and in the templates registered by plugins
     */
    protected function resolveFile(): ?string
    {
        if ($this->hasDefaultType() === true) {
            try {
                // Try the default template in the default template directory.
                return F::realpath($this->getFilename(), $this->root());
            } catch (Exception $e) {
                // ignore errors, continue searching
            }
            // Look for the default template provided by an extension.
            $path = Kirby::instance()->extension($this->store(), $this->name());
            if ($path !== null) {
                return $path;
            }
        }

        $name = $this->name().'.'.$this->type();

        try {
            // Try the template with type extension in the default template directory.
            return F::realpath($this->getFilename($name), $this->root());
        } catch (Exception $e) {
            // Look for the template with type extension provided by an extension.
            // This might be null if the template does not exist.
            return Kirby::instance()->extension($this->store(), $name);
        }
    }

    public function render(array $data = []): string
    {
        if ($this->isBlade() && $this->hasDefaultType() === true) {
            $this->blade = static::boot();

            // Templates registered by plugins live outside the
            // templates directory, so they are rendered by path
            $view = str_starts_with($this->file(), (realpath($this->root()) ?: $this->root()).'/')
                ? $this->blade->make($this->name, $data)
                : $this->blade->file($this->file(), $data);

            try {
                return $view->render();
            } finally {
                $this->blade->container()->terminate();
            }
        }

        return parent::render($data);
    }

    /**
     * Boots the Blade engine with the Kirby directives and if statements;
     * the engine is booted once and reused while the container is the same
     */
    public static function boot(): Blade
    {
        $container = static::getContainer(Application::getInstance());

        if (static::$booted?->container() === $container) {
            return static::$booted;
        }

        $views = static::getPathViews();

        if (! is_dir($views)) {
            Dir::make($views);
        }

        $blade = new Blade(static::getPathTemplates(), $views, $container);

        static::addViewLocation($blade, Snippet::getPathSnippets());
        static::setDirectives($blade);
        static::setIfStatements($blade);

        return static::$booted = $blade;
    }

    /**
     * Forgets the booted Blade engine, so the next boot starts fresh
     */
    public static function flush(): void
    {
        static::$booted = null;
    }

    /**
     * Adds a lookup location for views, only if it's not registered yet
     */
    protected static function addViewLocation(Blade $blade, string $path): void
    {
        $finder = $blade->container()->get('view')->getFinder();

        if (! in_array(realpath($path) ?: $path, $finder->getPaths(), true)) {
            $finder->addLocation($path);
        }
    }

    protected static function setDirectives(Blade $blade): void
    {
        $blade->compiler()->directive('js', function (string $path) {
            return "<?php echo Kirby\Cms\Html::js($path) ?>";
        });

        $blade->compiler()->directive('css', function (string $path) {
            return "<?php echo Kirby\Cms\Html::css($path) ?>";
        });

        $blade->compiler()->directive('kirbytext', function (string $text) {
            return "<?php echo Kirby\Cms\App::instance()->kirbytext($text) ?>";
        });

        $blade->compiler()->directive('kt', function (string $text) {
            return "<?php echo Kirby\Cms\App::instance()->kirbytext($text) ?>";
        });

        $blade->compiler()->directive('kirbytextinline', function (string $text) {
            return static::kirbytextInline($text);
        });

        $blade->compiler()->directive('kti', function (string $text) {
            return static::kirbytextInline($text);
        });

        $blade->compiler()->directive('smartypants', function (string $text) {
            return "<?php echo Kirby\Cms\App::instance()->smartypants($text) ?>";
        });

        $blade->compiler()->directive('esc', function (string $expression) {
            return "<?php echo Kirby\Toolkit\Str::esc($expression) ?>";
        });

        $blade->compiler()->directive('image', function ($text) {
            return "<?php echo Kirby\Cms\App::instance()->image($text) ?>";
        });

        $blade->compiler()->directive('svg', function (string $file) {
            return "<?php echo Kirby\Cms\Html::svg($file) ?>";
        });

        $blade->compiler()->directive('page', function (string $expression) {
            return trim($expression) === ''
                ? '<?php echo Kirby\Cms\App::instance()->site()->page() ?>'
                : "<?php echo Kirby\Cms\App::instance()->site()->find($expression) ?>";
        });

        $blade->compiler()->directive('pages', function (string $expression) {
            return "<?php echo Kirby\Cms\App::instance()->site()->find(array_merge(...array_map(Kirby\Toolkit\A::wrap(...), [$expression]))) ?>";
        });

        $blade->compiler()->directive('markdown', function (string $expression) {
            return "<?php echo Kirby\Cms\App::instance()->markdown($expression) ?>";
        });

        $blade->compiler()->directive('html', function (string $expression) {
            return "<?php echo Kirby\Cms\Html::encode($expression) ?>";
        });

        $blade->compiler()->directive('h', function (string $expression) {
            return "<?php echo Kirby\Cms\Html::encode($expression) ?>";
        });

        $blade->compiler()->directive('url', function (string $expression) {
            return "<?php echo Kirby\Cms\Url::to($expression) ?>";
        });

        $blade->compiler()->directive('u', function (string $expression) {
            return "<?php echo Kirby\Cms\Url::to($expression) ?>";
        });

        $blade->compiler()->directive('go', function (string $expression) {
            return "<?php Kirby\Http\Response::go($expression); ?>";
        });

        $blade->compiler()->directive('asset', function (string $path) {
            return "<?php echo new Kirby\Filesystem\Asset($path) ?>";
        });

        $blade->compiler()->directive('translate', function (string $expression) {
            return "<?php echo Kirby\Toolkit\I18n::translate($expression) ?>";
        });

        $blade->compiler()->directive('t', function (string $expression) {
            return "<?php echo Kirby\Toolkit\I18n::translate($expression) ?>";
        });

        $blade->compiler()->directive('lang', function (string $expression) {
            return "<?php echo Kirby\Toolkit\I18n::translate($expression) ?>";
        });

        $blade->compiler()->directive('tc', function (string $expression) {
            return "<?php echo Kirby\Toolkit\I18n::translateCount($expression) ?>";
        });

        $blade->compiler()->directive('choice', function (string $expression) {
            return "<?php echo Kirby\Toolkit\I18n::translateCount($expression) ?>";
        });

        $blade->compiler()->directive('tt', function (string $expression) {
            return "<?php echo Kirby\Toolkit\I18n::template($expression) ?>";
        });

        $blade->compiler()->directive('dump', function (string $expression) {
            return "<?php Kirby\Cms\Helpers::dump($expression) ?>";
        });

        $blade->compiler()->directive('csrf', function () {
            return '<?php echo Kirby\Cms\App::instance()->csrf() ?>';
        });

        $blade->compiler()->directive('snippet', function (string $expression) {
            return "<?php if (is_string(\$__snippet = Kirby\Cms\App::instance()->snippet($expression))) { echo \$__snippet; } unset(\$__snippet); ?>";
        });

        $blade->compiler()->directive('endsnippet', function () {
            return '<?php Kirby\Template\Snippet::end() ?>';
        });

        $blade->compiler()->directive('video', function (string $expression) {
            return "<?php echo Kirby\Cms\Html::video($expression) ?>";
        });

        $blade->compiler()->directive('vimeo', function (string $expression) {
            return "<?php echo Kirby\Cms\Html::vimeo($expression) ?>";
        });

        $blade->compiler()->directive('youtube', function (string $expression) {
            return "<?php echo Kirby\Cms\Html::youtube($expression) ?>";
        });

        $blade->compiler()->directive('gist', function (string $expression) {
            return "<?php echo Kirby\Cms\Html::gist($expression) ?>";
        });

        $blade->compiler()->directive('attr', function (string $expression) {
            return "<?php echo Kirby\Cms\Html::attr($expression) ?>";
        });

        $blade->compiler()->directive('vite', function (string $expression) {
            return "<?php echo (new Beebmx\KirbyBlade\Foundation\Vite)($expression); ?>";
        });

        $blade->compiler()->directive('viteReactRefresh', function () {
            return '<?php echo (new Beebmx\KirbyBlade\Foundation\Vite)->reactRefresh(); ?>';
        });

        foreach (Kirby::instance()->option('beebmx.kirby-blade.directives', []) as $directive => $callback) {
            $blade->compiler()->directive($directive, $callback);
        }
    }

    /**
     * Compiles kirbytext with inline markdown, so no wrapping paragraph is added
     */
    protected static function kirbytextInline(string $expression): string
    {
        return "<?php echo (fn (?string \$text = null, array \$options = []) => Kirby\Cms\App::instance()->kirbytext(\$text, array_replace_recursive(\$options, ['markdown' => ['inline' => true]])))($expression) ?>";
    }

    protected static function setIfStatements(Blade $blade): void
    {
        $blade->compiler()->if('auth', function () {
            return Kirby::instance()->user() !== null;
        });

        $blade->compiler()->if('guest', function () {
            return Kirby::instance()->user() === null;
        });

        foreach (Kirby::instance()->option('beebmx.kirby-blade.ifs', []) as $statement => $callback) {
            $blade->compiler()->if($statement, $callback);
        }
    }

    public function getFilename(?string $name = null): string
    {
        if ($name) {
            return $this->root().'/'.$name.'.'.$this->extension();
        }

        return file_exists($this->root().'/'.$this->name().'.'.$this->bladeExtension())
            ? $this->root().'/'.$this->name().'.'.$this->bladeExtension()
             : $this->root().'/'.$this->name().'.'.$this->extension();
    }

    public function isBlade(): bool
    {
        return str_ends_with($this->file() ?? '', static::BLADE_EXTENSION);
    }

    /**
     * Returns the expected template file extension
     */
    public function bladeExtension(): string
    {
        return Template::BLADE_EXTENSION;
    }

    public static function getPathTemplates(): string
    {
        return Kirby::instance()->roots()->templates();
    }

    public static function getPathViews(): string
    {
        $path = Kirby::instance()->option('beebmx.kirby-blade.views');

        if (is_callable($path)) {
            return $path();
        }

        return $path;
    }

    public static function getContainer(ContainerInterface $container): ContainerInterface
    {
        return method_exists($container, 'terminate')
            ? $container
            : Application::getInstance();
    }
}
