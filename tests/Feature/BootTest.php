<?php

use Beebmx\KirbyBlade\Facades\Blade as BladeFacade;
use Beebmx\KirbyBlade\Template;
use Illuminate\Container\Container;
use Illuminate\Contracts\View\View;

it('boots blade when the plugins are loaded by default', function () {
    App();

    expect(Container::getInstance()->has('blade.compiler'))->toBeTrue();
});

it('does not boot blade when the plugins are loaded and bootstrap is disabled', function () {
    App(options: ['beebmx.kirby-blade.bootstrap' => false]);

    expect(Container::getInstance()->has('blade.compiler'))->toBeFalse();
});

it('boots blade only once per container', function () {
    App();

    expect(Template::boot())->toBe(Template::boot());
});

it('boots blade again after flushing', function () {
    App();
    $blade = Template::boot();

    Template::flush();

    expect(Template::boot())->not->toBe($blade);
});

it('resolves the facade without bootstrapping the plugin', function () {
    App(options: ['beebmx.kirby-blade.bootstrap' => false]);

    BladeFacade::directive('hello', fn () => '<?php echo "hello" ?>');

    expect(Template::boot()->compiler()->getCustomDirectives())->toHaveKey('hello');
});

it('resolves the view helper without bootstrapping the plugin', function () {
    App(options: ['beebmx.kirby-blade.bootstrap' => false]);

    expect(view('partials.greeting', ['site' => site()]))->toBeInstanceOf(View::class)
        ->and(view('partials.greeting', ['site' => site()])->render())->toContain('Greetings from Fixtures');
});

it('creates the compiled views directory when blade boots', function () {
    $views = cachePath().'/compiled-'.uniqid();
    App(options: ['beebmx.kirby-blade.bootstrap' => false, 'beebmx.kirby-blade.views' => $views]);

    expect($views)->not->toBeDirectory();

    new Template('home');

    expect($views)->not->toBeDirectory();

    Template::boot();

    expect($views)->toBeDirectory();
});
