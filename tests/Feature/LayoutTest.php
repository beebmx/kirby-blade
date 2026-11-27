<?php

it('extends a blade layout', function () {
    expect(render(App(), 'layout'))
        ->toStartWith('<!doctype html>')
        ->toContain('<title>Fixtures | Layout</title>')
        ->toContain('<h1>Layout: Layout</h1>')
        ->toEndWith('</html>');
});

it('passes the controller data to blade templates', function () {
    expect(render(App(), 'controller'))
        ->toContain('<title>Fixtures | Controller</title>')
        ->toContain('<p>Blade Controller: bar</p>');
});

it('renders content representations with their own controller', function () {
    $kirby = App();
    $page = $kirby->page('controller');
    $kirby->site()->visit($page);

    expect(json_decode($page->render(contentType: 'json'), true))
        ->toBe(['success' => true, 'foo' => 'json']);
});

it('prefers the blade template over a php template with the same name', function () {
    expect(render(App(), 'home'))
        ->toContain('<h1>Home</h1>')
        ->not->toContain('PHP home');
});
