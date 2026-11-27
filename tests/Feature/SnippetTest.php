<?php

use Beebmx\KirbyBlade\Snippet;
use Beebmx\KirbyBlade\Template;
use Kirby\Exception\InvalidArgumentException;

it('renders blade snippets inside php templates', function () {
    expect(render(App(), 'about'))
        ->toContain('<p class="greeting">Hello Blade <em>inline</em></p>');
});

it('renders blade snippets without bootstrapping the plugin', function () {
    expect(render(App(options: ['beebmx.kirby-blade.bootstrap' => false]), 'about'))
        ->toContain('<p class="greeting">Hello Blade <em>inline</em></p>');
});

it('renders the snippet instead of a template with the same name', function () {
    expect(render(App(), 'collision'))
        ->toBe('SNIPPET card');
});

it('does not register the snippets location more than once', function () {
    $kirby = App();
    $finder = Template::boot()->container()->get('view')->getFinder();
    $paths = count($finder->getPaths());

    $kirby->snippet('greeting', ['name' => 'one']);
    $kirby->snippet('greeting', ['name' => 'two']);

    expect($finder->getPaths())->toHaveCount($paths);
});

it('falls back to kirby for php snippets and fallback names', function () {
    $kirby = App();

    expect($kirby->snippet(['missing', 'plain'], ['label' => 'fallback', 'page' => $kirby->page('home')]))
        ->toContain('<div class="plain">[fallback on Home]</div>');
});

it('detects blade snippets by their file name', function () {
    App();

    expect(Snippet::isBlade('greeting'))->toBeTrue()
        ->and(Snippet::isBlade('plain'))->toBeFalse()
        ->and(Snippet::isBlade('missing'))->toBeFalse()
        ->and(Snippet::isBlade(null))->toBeFalse();
});

it('detects blade snippets in a list of fallback names like kirby', function () {
    App();

    expect(Snippet::isBlade(['missing', 'greeting']))->toBeTrue()
        ->and(Snippet::isBlade(['plain', 'greeting']))->toBeFalse()
        ->and(Snippet::isBlade(['missing', 'other']))->toBeFalse();
});

it('renders the first existing blade snippet from a list of fallback names', function () {
    $kirby = App();

    expect($kirby->snippet(['missing', 'greeting'], ['name' => 'fallback']))
        ->toContain('<p class="greeting">Hello fallback');
});

it('renders blade snippets registered by plugins', function () {
    $kirby = App(props: [
        'snippets' => [
            'plugin-card' => fixtures('plugin/snippets/plugin-card.blade.php'),
            'plugin-plain' => fixtures('site/snippets/plain.php'),
        ],
    ]);

    expect(Snippet::isBlade('plugin-card'))->toBeTrue()
        ->and(Snippet::isBlade('plugin-plain'))->toBeFalse()
        ->and($kirby->snippet('plugin-card', ['name' => 'Kirby']))
        ->toBe('<p class="plugin-snippet">From plugin Kirby</p>'."\n");
});

it('does not look for blade snippets outside the snippets directory', function () {
    App();

    expect(Snippet::isBlade('../templates/card'))->toBeFalse();
});

it('renders blade snippets with the default slot from php templates', function () {
    expect(render(App(), 'slots'))
        ->toContain('<section class="slotted"> <h2>Default slot</h2> <p>From PHP</p> </section>');
});

it('renders blade snippets with named slots from php templates', function () {
    expect(render(App(), 'slots'))
        ->toContain('<h2>Named slots</h2> <p>Named default</p> <footer>Footer</footer> </section>');
});

it('renders blade snippets with slots from blade templates', function () {
    expect(render(App(), 'slots-blade'))
        ->toContain('<section class="slotted"> <h2>Blade slot</h2> <p>From Blade</p> </section>')
        ->toContain('<section class="slotted"> <h2>Without slots</h2> </section>');
});

it('passes the kirby data to blade snippets', function () {
    $kirby = App();
    $kirby->site()->visit('about');

    expect($kirby->snippet('greeting', ['name' => 'Data']))
        ->toContain('<p class="greeting">Hello Data');
});

it('does not allow passing the slot variables to blade snippets', function () {
    App()->snippet('slotted', ['title' => 'Slot', 'slot' => 'Not allowed']);
})->throws(InvalidArgumentException::class, 'Passing the $slot or $slots variables to snippets is not supported.');
