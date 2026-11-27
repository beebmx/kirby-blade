<?php

it('renders anonymous components with their attributes', function () {
    expect(render(App(), 'components'))
        ->toContain('<h2 class="font-bold">Components</h2>');
});

it('merges the attributes of anonymous components', function () {
    expect(render(App(), 'components'))
        ->toContain('<div class="container mb-4">Inside container</div>');
});

it('renders nested anonymous components', function () {
    expect(render(App(), 'components'))
        ->toContain('<div class="container-full mb-4">Inside full container</div>');
});

it('renders named slots', function () {
    expect(render(App(), 'components'))
        ->toContain('<div class="alert-title">Success!</div>')
        ->toContain('<div class="alert-body">Great job!</div>');
});

it('renders dynamic components', function () {
    expect(render(App(), 'components'))
        ->toContain('<div class="placeholder dynamic">Blade Component Placeholder</div>');
});

it('renders class components', function () {
    expect(render(App(), 'components'))
        ->toContain('<div class="with-class">With Class: Hello, World!!!!</div>')
        ->toContain('<div class="with-class">With Class: Hi Kirby!!!</div>');
});

it('renders class components with a layout view', function () {
    expect(render(App(), 'components'))
        ->toContain('<div class="layout"> <div>I\'m a layout!</div> Hi Friend! </div>');
});

it('renders anonymous components from a prefixed path registered in a hook', function () {
    $kirby = AppWithCustomDirectives();

    expect(render($kirby, 'custom'))
        ->toContain('This is an extra component from anonymousComponentPath');
});

it('escapes the bound attributes of class components', function () {
    expect(render(App(), 'dynamic'))
        ->toContain('<div class="profile" title="&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;">Static</div>');
});

it('escapes the bound attributes of dynamic class components', function () {
    expect(render(App(), 'dynamic'))
        ->toContain('<div class="profile" title="&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;">Dynamic</div>')
        ->not->toContain('<script>');
});

it('escapes the bound data of dynamic class components when printed', function () {
    expect(render(App(), 'dynamic'))
        ->toContain('<div class="profile" >&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;</div>');
});

it('escapes the bound attributes of dynamic anonymous components', function () {
    expect(render(App(), 'dynamic'))
        ->toContain('title="&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;">Blade Component Placeholder</div>');
});

it('renders anonymous components without a composer file', function () {
    $index = temporarySite();
    unlink($index.'/composer.json');

    expect(render(App(roots: ['index' => $index]), 'home'))
        ->toContain('<span class="badge">component</span>');
});

it('renders anonymous components without an application namespace', function () {
    $index = temporarySite();
    file_put_contents($index.'/composer.json', '{"require": {"getkirby/cms": "*"}}');

    expect(render(App(roots: ['index' => $index]), 'home'))
        ->toContain('<span class="badge">component</span>');
});
