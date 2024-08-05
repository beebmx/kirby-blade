<?php

namespace Beebmx\KirbyBlade;

use Beebmx\Blade\Container as KirbyContainer;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use Kirby\Cms\App as Kirby;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Template\Template as KirbyTemplate;
use Kirby\Toolkit\Tpl;

class Template extends KirbyTemplate
{
    public const BLADE_EXTENSION = 'blade.php';

    protected Blade $blade;

    protected string $views;

    protected string $defaultType;

    protected string $name;

    protected string $template;

    protected string $type;

    public static array $data = [];

    public function __construct(Kirby $kirby, string $name, string $type = 'html', string $defaultType = 'html')
    {
        $this->template = static::getPathTemplates();
        $this->views = static::getPathViews();

        $this->name = strtolower($name);
        $this->type = $type;
        $this->defaultType = $defaultType;

        $this->setViewDirectory();
    }

    /**
     * Detects the location of the template file
     * if it exists.
     */
    public function file(): ?string
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
        if ($this->isBlade()) {
            $application = static::getContainer(
                Container::getInstance()
            );

            $this->blade = new Blade(
                $this->template,
                $this->views,
                $application
            );

            $this->setDirectives();
            $this->setIfStatements();

            if ($this->hasDefaultType() === true) {
                return tap($this->blade->make($this->name, $data), function () use ($application) {
                    $application->terminate();
                });
            }
        }

        return Tpl::load($this->file(), $data);
    }

    public function setViewDirectory(): void
    {
        if (! file_exists($this->views)) {
            Dir::make($this->views);
        }
    }

    protected function setDirectives(): void
    {
        $this->blade->compiler()->directive('js', function (string $path) {
            return "<?php echo Kirby\Cms\Html::js($path) ?>";
        });

        $this->blade->compiler()->directive('css', function (string $path) {
            return "<?php echo Kirby\Cms\Html::css($path) ?>";
        });

        $this->blade->compiler()->directive('kirbytext', function (string $text) {
            return "<?php echo kirbytext($text) ?>";
        });

        $this->blade->compiler()->directive('kt', function (string $text) {
            return "<?php echo kirbytext($text) ?>";
        });

        $this->blade->compiler()->directive('kirbytextinline', function (string $text) {
            return "<?php echo kirbytextinline($text) ?>";
        });

        $this->blade->compiler()->directive('kti', function (string $text) {
            return "<?php echo kirbytextinline($text) ?>";
        });

        $this->blade->compiler()->directive('smartypants', function (string $text) {
            return "<?php echo Kirby\Cms\App::instance()->smartypants($text) ?>";
        });

        $this->blade->compiler()->directive('esc', function (string $expression) {
            return "<?php echo Kirby\Toolkit\Str::esc($expression) ?>";
        });

        $this->blade->compiler()->directive('image', function ($text) {
            return "<?php echo Kirby\Cms\App::instance()->image($text) ?>";
        });

        $this->blade->compiler()->directive('svg', function (string $file) {
            return "<?php echo Kirby\Cms\Html::svg($file) ?>";
        });

        $this->blade->compiler()->directive('page', function (mixed $page) {
            return "<?php echo page($page) ?>";
        });

        $this->blade->compiler()->directive('pages', function (mixed $page) {
            return "<?php echo pages($page) ?>";
        });

        $this->blade->compiler()->directive('markdown', function (string $expression) {
            return "<?php echo Kirby\Cms\App::instance()->markdown($expression) ?>";
        });

        $this->blade->compiler()->directive('html', function (string $expression) {
            return "<?php echo Kirby\Cms\Html::encode($expression) ?>";
        });

        $this->blade->compiler()->directive('h', function (string $expression) {
            return "<?php echo Kirby\Cms\Html::encode($expression) ?>";
        });

        $this->blade->compiler()->directive('url', function (string $expression) {
            return "<?php echo Kirby\Cms\Url::to($expression) ?>";
        });

        $this->blade->compiler()->directive('u', function (string $expression) {
            return "<?php echo Kirby\Cms\Url::to($expression) ?>";
        });

        $this->blade->compiler()->directive('go', function (string $url, int $code = 302) {
            return "<?php echo Kirby\Http\Response::go($url, $code); ?>";
        });

        $this->blade->compiler()->directive('asset', function (string $path) {
            return "<?php echo new Kirby\Filesystem\Asset($path) ?>";
        });

        $this->blade->compiler()->directive('translate', function (string $expression) {
            return "<?php echo Kirby\Toolkit\I18n::translate($expression) ?>";
        });

        $this->blade->compiler()->directive('t', function (string $expression) {
            return "<?php echo Kirby\Toolkit\I18n::translate($expression) ?>";
        });

        $this->blade->compiler()->directive('tc', function (string $expression) {
            return "<?php echo Kirby\Toolkit\I18n::translateCount($expression) ?>";
        });

        $this->blade->compiler()->directive('tt', function (string $expression) {
            return "<?php echo Kirby\Toolkit\I18n::template($expression) ?>";
        });

        $this->blade->compiler()->directive('dump', function (string $expression) {
            return "<?php echo Kirby\Cms\Helpers::dump($expression) ?>";
        });

        $this->blade->compiler()->directive('csrf', function () {
            return '<?php echo Kirby\Cms\App::instance()->csrf() ?>';
        });

        $this->blade->compiler()->directive('snippet', function (string $expression) {
            return "<?php snippet($expression) ?>";
        });

        $this->blade->compiler()->directive('twitter', function (string $expression) {
            return "<?php echo twitter($expression) ?>";
        });

        $this->blade->compiler()->directive('video', function (string $expression) {
            return "<?php echo Kirby\Cms\Html::video($expression) ?>";
        });

        $this->blade->compiler()->directive('vimeo', function (string $expression) {
            return "<?php echo Kirby\Cms\Html::vimeo($expression) ?>";
        });

        $this->blade->compiler()->directive('youtube', function (string $expression) {
            return "<?php echo Kirby\Cms\Html::youtube($expression) ?>";
        });

        $this->blade->compiler()->directive('gist', function (string $url, ?string $file = null) {
            return "<?php echo Kirby\Cms\App::instance()->kirbytag(['gist' => $url, 'file' => $file]) ?>";
        });

        $this->blade->compiler()->directive('vite', function ($entrypoints, $buildDirectory = 'build') {
            return "<?php echo (new Beebmx\KirbyBlade\Foundation\Vite)($entrypoints, '$buildDirectory'); ?>";
        });

        foreach (Kirby::instance()->option('beebmx.kirby-blade.directives', []) as $directive => $callback) {
            $this->blade->compiler()->directive($directive, $callback);
        }
    }

    protected function setIfStatements(): void
    {
        $this->blade->compiler()->if('auth', function () {
            return Kirby::instance()->user() !== null;
        });

        $this->blade->compiler()->if('guest', function () {
            return Kirby::instance()->user() === null;
        });

        foreach (Kirby::instance()->option('beebmx.kirby-blade.ifs', []) as $statement => $callback) {
            $this->blade->compiler()->if($statement, $callback);
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
        return str_ends_with($this->file(), static::BLADE_EXTENSION);
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
            : new KirbyContainer;
    }
}
