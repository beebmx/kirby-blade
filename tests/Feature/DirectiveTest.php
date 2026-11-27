<?php

use Beebmx\KirbyBlade\Facades\Blade;

describe('kirby directives', function () {
    beforeEach(function () {
        $this->html = render(App(props: [
            'translations' => [
                'en' => [
                    'greeting' => 'Hello translation',
                    'apples' => ['No apples', 'One apple', '{{ count }} apples'],
                ],
            ],
        ]), 'directives');
    });

    it('renders kirbytext', function () {
        expect($this->html)
            ->toContain('<div class="kt"><p><strong>Kirbytext</strong></p></div>')
            ->toContain('<div class="kti"><em>inline</em></div>')
            ->toContain('<div class="kti-breaks">One<br /> Two</div>')
            ->toContain('<div class="kti-options">One Two</div>');
    });

    it('escapes and encodes html', function () {
        expect($this->html)
            ->toContain('<div class="esc">&lt;b&gt;escaped&lt;/b&gt;</div>')
            ->toContain('<div class="html">&lt;i&gt;encoded&lt;/i&gt;</div>');
    });

    it('renders markdown', function () {
        expect($this->html)->toContain('<div class="markdown"><h1>Markdown</h1></div>');
    });

    it('renders urls and assets', function () {
        expect($this->html)
            ->toContain('<div class="url">http://example.com/about</div>')
            ->toContain('<div class="css"><link href="http://example.com/assets/app.css" rel="stylesheet"></div>');
    });

    it('renders the csrf token', function () {
        expect($this->html)->toMatch('#<div class="csrf">[a-f0-9]{64}</div>#');
    });

    it('renders the vite assets from the manifest', function () {
        expect($this->html)
            ->toContain('<link rel="stylesheet" href="http://example.com/build/assets/app-C_Q8FkwN.css" />')
            ->toContain('<script type="module" src="http://example.com/build/assets/app-CxBqiQ7E.js"></script>');
    });

    it('renders blade snippets', function () {
        expect($this->html)
            ->toContain('<div class="snippet"><p class="greeting">Hello Directive <em>inline</em></p> </div>');
    });

    it('renders pages', function () {
        expect($this->html)
            ->toContain('<div class="page">about</div>')
            ->toContain('<div class="current">directives</div>')
            ->toContain('<div class="pages">about<br />home</div>')
            ->toContain('<div class="pages-array">about<br />home</div>');
    });

    it('renders gists', function () {
        expect($this->html)
            ->toContain('<div class="gist"><script src="https://gist.github.com/beebmx/1.js"></script></div>')
            ->toContain('<div class="gist-file"><script src="https://gist.github.com/beebmx/1.js?file=example.php"></script></div>');
    });

    it('renders html attributes', function () {
        expect($this->html)
            ->toContain('<div class="attr" data-id="7" hidden></div>')
            ->toContain('<div class="attr-pair" aria-label="Close"></div>');
    });

    it('renders translations with the laravel directives', function () {
        expect($this->html)
            ->toContain('<div class="lang">Hello translation</div>')
            ->toContain('<div class="lang-fallback">Fallback</div>')
            ->toContain('<div class="choice">One apple / 3 apples</div>');
    });

    it('dumps a variable only once', function () {
        expect(substr_count($this->html, '[dumped] => 1'))->toBe(1);
    });

    it('renders nothing for the react refresh without the vite dev server', function () {
        expect($this->html)->toContain('<div class="react"></div>');
    });

    it('supports the illuminate helpers', function () {
        expect($this->html)->toContain('<div class="str">str()-&gt;lower()</div>');
    });

    it('supports the blade directives', function () {
        expect(substr_count($this->html, 'Hello Once directive'))->toBe(1)
            ->and($this->html)->toContain('<div class="guest">Guest</div>');
    });
});

it('renders the react refresh with the vite dev server', function () {
    $index = temporarySite();
    file_put_contents($index.'/hot', 'http://localhost:5173');

    expect(render(App(roots: ['index' => $index]), 'directives'))
        ->toContain("import RefreshRuntime from 'http://localhost:5173/@react-refresh'")
        ->toContain('<script type="module" src="http://localhost:5173/@vite/client"></script>');
});

describe('compiled directives', function () {
    beforeEach(fn () => App());

    it('passes the redirect status code', function () {
        expect(Blade::compileString("@go('/about', 301)"))
            ->toBe("<?php Kirby\\Http\\Response::go('/about', 301); ?>");
    });

    it('passes the vite build directory', function () {
        expect(Blade::compileString("@vite('resources/js/app.js', 'dist')"))
            ->toBe("<?php echo (new Beebmx\\KirbyBlade\\Foundation\\Vite)('resources/js/app.js', 'dist'); ?>");
    });
});

describe('custom directives', function () {
    it('registers custom if statements with arguments', function () {
        expect(render(AppWithCustomDirectives('testing'), 'custom'))
            ->toContain('Testing environment')
            ->not->toContain('Production environment');

        expect(render(AppWithCustomDirectives('production'), 'custom'))
            ->toContain('Production environment')
            ->not->toContain('Testing environment');
    });

    it('registers custom if statements without arguments', function () {
        expect(render(AppWithCustomDirectives(), 'custom'))
            ->toContain('<div class="amiguest">I am a guest</div>');
    });

    it('registers custom directives', function () {
        expect(render(AppWithCustomDirectives(), 'custom'))
            ->toContain('<div class="upper">SHOUT</div>');
    });
});
