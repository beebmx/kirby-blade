<?php

namespace Beebmx;

use Kirby\Cms\App as Kirby;
use Kirby\Cms\Template as KirbyTemplate;
use Beebmx\Blade\Blade;
use Exception;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Tpl;
use Kirby\Toolkit\Dir;

class Template extends KirbyTemplate
{
    protected $blade;
    protected $views;
    protected $defaultType;
    protected $name;
    protected $template;
    protected $type;
    public static $data = [];

    public function __construct(Kirby $kirby, string $name, string $type = 'html', string $defaultType = 'html')
    {
        $this->template = $kirby->roots()->templates();
        $this->views = $this->getPathViews();

        $this->name = strtolower($name);
        $this->type = $type;
        $this->defaultType = $defaultType;

        $this->setViewDirectory();
    }

    /**
     * Detects the location of the template file
     * if it exists.
     *
     * @return string|null
     */
    public function file(): ?string
    {
        if ($this->hasDefaultType() === true) {
            try {
                // Try the default template in the default template directory.
                return F::realpath($this->getFilename(), $this->root());
            } catch (Exception $e) {
                //
            }
            // Look for the default template provided by an extension.
            $path = Kirby::instance()->extension($this->store(), $this->name());
            if ($path !== null) {
                return $path;
            }
        }
        $name = $this->name() . '.' . $this->type();
        try {
            // Try the template with type extension in the default template directory.
            return F::realpath($this->getFilename(), $this->root());
        } catch (Exception $e) {
            // Look for the template with type extension provided by an extension.
            // This might be null if the template does not exist.
            return Kirby::instance()->extension($this->store(), $name);
        }
    }

    /**
     * @param array $data
     * @return string
     */
    public function render(array $data = []): string
    {
        if ($this->isBlade()) {
            $this->blade = new Blade(
                $this->template,
                $this->views
            );
            $this->setDirectives();
            $this->setIfStatements();

            return $this->blade->make($this->name, $data);
        } else {
            return Tpl::load($this->file(), $data);
        }
    }

    public function setViewDirectory()
    {
        if (!file_exists($this->views)) {
            Dir::make($this->views);
        }
    }

    protected function setDirectives()
    {
        $this->blade->compiler()->directive('js', function ($path) {
            return "<?php echo js($path) ?>";
        });

        $this->blade->compiler()->directive('css', function ($path) {
            return "<?php echo css($path) ?>";
        });

        $this->blade->compiler()->directive('kirbytext', function ($text) {
            return "<?php echo kirbytext($text) ?>";
        });

        $this->blade->compiler()->directive('kt', function ($text) {
            return "<?php echo kirbytext($text) ?>";
        });

        $this->blade->compiler()->directive('kirbytextinline', function ($text) {
            return "<?php echo kirbytextinline($text) ?>";
        });

        $this->blade->compiler()->directive('kti', function ($text) {
            return "<?php echo kirbytextinline($text) ?>";
        });

        $this->blade->compiler()->directive('smartypants', function ($text) {
            return "<?php echo smartypants($text) ?>";
        });

        $this->blade->compiler()->directive('esc', function (string $string, string $context = 'html', bool $strict = false) {
            return "<?php echo esc($string, $context, $strict) ?>";
        });

        $this->blade->compiler()->directive('image', function ($text) {
            return "<?php echo image($text) ?>";
        });

        $this->blade->compiler()->directive('svg', function ($file) {
            return "<?php echo svg($file) ?>";
        });

        $this->blade->compiler()->directive('page', function (mixed $page) {
            return "<?php echo page($page) ?>";
        });

        $this->blade->compiler()->directive('pages', function (mixed $page) {
            return "<?php echo pages($page) ?>";
        });

        $this->blade->compiler()->directive('markdown', function ($text) {
            return "<?php echo markdown($text) ?>";
        });

        $this->blade->compiler()->directive('html', function ($string, bool $tags = false) {
            if ($tags) {
                return "<?php echo html($string, $tags) ?>";
            }
            return "<?php echo html($string) ?>";
        });

        $this->blade->compiler()->directive('h', function ($string, bool $tags = false) {
            if ($tags) {
                return "<?php echo html($string, $tags) ?>";
            }
            return "<?php echo html($string) ?>";
        });

        $this->blade->compiler()->directive('url', function ($path) {
            return "<?php echo url($path) ?>";
        });

        $this->blade->compiler()->directive('u', function ($path) {
            return "<?php echo u($path) ?>";
        });

        $this->blade->compiler()->directive('go', function (string $path, int $code = 302) {
            return "<?php echo go($path, $code) ?>";
        });

        $this->blade->compiler()->directive('asset', function ($path) {
            return "<?php echo asset($path) ?>";
        });

        $this->blade->compiler()->directive('translate', function ($text, $fallback) {
            return "<?php echo t($text, $fallback) ?>";
        });

        $this->blade->compiler()->directive('t', function ($text, $fallback = null) {
            return "<?php echo t($text, $fallback) ?>";
        });

        $this->blade->compiler()->directive('tc', function ($text, $count) {
            return "<?php echo tc($text, $count) ?>";
        });

        $this->blade->compiler()->directive('dump', function ($variable) {
            return "<?php echo dump($variable) ?>";
        });

        $this->blade->compiler()->directive('csrf', function () {
            return '<?php echo csrf() ?>';
        });

        $this->blade->compiler()->directive('snippet', function ($name, mixed $data = null) {
            if ($data) {
                return "<?php snippet($name, $data) ?>";
            }
            return "<?php snippet($name) ?>";
        });

        $this->blade->compiler()->directive('twitter', function (string $username, string $text = null, string $title = null, string $class = null) {
            return "<?php echo twitter($username, $text, $title, $class) ?>";
        });

        $this->blade->compiler()->directive('video', function (string $url, array $options = [], array $attr = []) {
            return "<?php echo video($url, $options, $attr) ?>";
        });

        $this->blade->compiler()->directive('vimeo', function (string $url, array $options = [], array $attr = []) {
            return "<?php echo vimeo($url, $options, $attr) ?>";
        });

        $this->blade->compiler()->directive('youtube', function (string $url, array $options = [], array $attr = []) {
            return "<?php echo youtube($url, $options, $attr) ?>";
        });

        $this->blade->compiler()->directive('gist', function (string $url, string $file = null) {
            return "<?php echo gist($url, $url, $file) ?>";
        });

        foreach ($directives = option('beebmx.kirby-blade.directives', []) as $directive => $callback) {
            $this->blade->compiler()->directive($directive, $callback);
        }
    }

    protected function setIfStatements()
    {
        foreach ($statements = option('beebmx.kirby-blade.ifs', []) as $statement => $callback) {
            $this->blade->compiler()->if($statement, $callback);
        }
    }

    public function getFilename()
    {
        if ($this->isBlade()) {
            return $this->root() . '/' . $this->name() . '.' . $this->bladeExtension();
        } else {
            return $this->root() . '/' . $this->name() . '.' . $this->extension();
        }
    }

    public function isBlade()
    {
        return !!file_exists($this->template . '/' . $this->name() . '.' . $this->bladeExtension());
    }

    /**
     * Returns the expected template file extension
     *
     * @return string
     */
    public function bladeExtension(): string
    {
        return 'blade.php';
    }

    protected function getPathViews()
    {
        $path = option('beebmx.kirby-blade.views');
        if (is_callable($path)) {
            return $path();
        }
        return $path;
    }
}
