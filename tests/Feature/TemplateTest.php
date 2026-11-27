<?php

use Beebmx\KirbyBlade\Template;
use Illuminate\View\ViewException;

it('renders a blade template with the kirby data', function () {
    expect(render(App(), 'home'))
        ->toContain('<h1>Home</h1>')
        ->toContain('<p>Greetings from Fixtures</p>');
});

it('renders php snippets inside blade templates', function () {
    expect(render(App(), 'home'))
        ->toContain('<div class="plain">[php-snippet on Home]</div>');
});

it('renders anonymous blade components', function () {
    expect(render(App(), 'home'))
        ->toContain('<span class="badge">component</span>');
});

it('registers the kirby directives', function () {
    expect(render(App(), 'home'))
        ->toContain('<p>Hello <strong>Kirby</strong></p>');
});

it('registers the auth and guest if statements', function () {
    expect(render(App(), 'home'))
        ->toContain('guest')
        ->not->toContain('member');
});

it('registers custom directives from the options', function () {
    $kirby = App(options: [
        'beebmx.kirby-blade.directives' => [
            'shout' => fn (string $text) => "<?php echo strtoupper($text) ?>",
        ],
    ]);

    expect($kirby->option('beebmx.kirby-blade.directives'))->toHaveKey('shout')
        ->and(Template::boot()->compiler()->getCustomDirectives())
        ->toHaveKey('shout');
});

it('registers custom if statements from the options', function () {
    App(options: [
        'beebmx.kirby-blade.ifs' => [
            'enabled' => fn () => true,
        ],
    ]);

    expect(Template::boot()->compiler()->check('enabled'))->toBeTrue();
});

it('renders regular php templates with slots', function () {
    expect(render(App(), 'about'))
        ->toStartWith('<html><body>')
        ->toContain('About: About')
        ->toContain('<div class="plain">[php-snippet on About]</div>')
        ->toEndWith('</body></html>');
});

it('renders regular php templates with plain snippets', function () {
    expect(render(App(), 'legacy'))
        ->toContain('<main>Legacy: Legacy</main>');
});

it('supports template stacks', function () {
    expect(render(App(), 'stack'))
        ->toStartWith('<html><head><meta name="pushed"></head><body>')
        ->toContain('Stack page');
})->skip(fn () => ! isKirby('6.0.0'), 'Template stacks require Kirby 6');

it('renders blade templates registered by plugins', function () {
    $kirby = App(props: [
        'templates' => [
            'plugin-page' => fixtures('plugin/templates/plugin-page.blade.php'),
        ],
    ]);

    expect(render($kirby, 'plugin-page'))
        ->toBe('<p class="plugin-template">Plugin Page from plugin</p>');
});

it('resolves the template file only once', function () {
    $kirby = App(roots: ['index' => temporarySite()]);
    $template = $kirby->template('home');
    $file = $template->file();

    rename($file, $file.'.moved');

    expect($file)->toEndWith('home.blade.php')
        ->and($template->file())->toBe($file)
        ->and($kirby->template('home')->file())->not->toBe($file);
});

it('surfaces the original exception thrown inside a blade template', function () {
    render(App(), 'broken');
})->throws(ViewException::class, 'Call to undefined function this_function_does_not_exist()');
