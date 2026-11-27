<?php

use Beebmx\KirbyBlade\Console\MakeComponentCommand;
use Beebmx\KirbyBlade\Console\ViewClearCommand;

it('clears the compiled views', function (bool $bootstrap) {
    $kirby = App(options: ['beebmx.kirby-blade.bootstrap' => $bootstrap]);
    render($kirby, 'home');

    $views = $kirby->option('beebmx.kirby-blade.views')();
    expect(glob($views.'/*'))->not->toBeEmpty();

    (new ViewClearCommand)($cli = cli());

    expect(glob($views.'/*'))->toBeEmpty()
        ->and($cli->output)->toBe(['Compiled views cleared successfully.']);
})->with([
    'bootstrap enabled' => true,
    'bootstrap disabled' => false,
]);

it('clears the compiled views before blade is booted', function () {
    App(options: ['beebmx.kirby-blade.bootstrap' => false]);

    (new ViewClearCommand)($cli = cli());

    expect($cli->output)->toBe(['Compiled views cleared successfully.']);
});

it('makes an anonymous component view', function (bool $bootstrap) {
    $index = temporarySite();
    App(roots: ['index' => $index], options: ['beebmx.kirby-blade.bootstrap' => $bootstrap]);

    (new MakeComponentCommand)($cli = cli(['name' => 'notice', 'view' => true]));

    expect($index.'/site/templates/components/notice.blade.php')->toBeFile()
        ->and($cli->output[0])->toContain('created successfully');
})->with([
    'bootstrap enabled' => true,
    'bootstrap disabled' => false,
]);

it('makes a component class and its view', function () {
    $index = temporarySite();
    App(roots: ['index' => $index]);

    (new MakeComponentCommand)($cli = cli(['name' => 'Banner']));

    expect($index.'/app/View/Components/Banner.php')->toBeFile()
        ->and(file_get_contents($index.'/app/View/Components/Banner.php'))
        ->toContain('namespace App\View\Components;')
        ->toContain('class Banner')
        ->toContain("view('components.banner')")
        ->and($index.'/site/templates/components/banner.blade.php')->toBeFile()
        ->and($cli->output[0])->toBe('Component [/app/View/Components/Banner.php] created successfully.');
});

it('does not make the view when the component class already exists', function () {
    $index = temporarySite();
    App(roots: ['index' => $index]);
    file_put_contents($index.'/app/View/Components/Orphan.php', '<?php // existing');

    (new MakeComponentCommand)($cli = cli(['name' => 'Orphan']));

    expect($cli->output)->toBe(['Component already exists.'])
        ->and(file_get_contents($index.'/app/View/Components/Orphan.php'))->toBe('<?php // existing')
        ->and($index.'/site/templates/components/orphan.blade.php')->not->toBeFile();
});

it('does not make the view when the component name is reserved', function () {
    $index = temporarySite();
    App(roots: ['index' => $index]);

    (new MakeComponentCommand)($cli = cli(['name' => 'Class']));

    expect($cli->output)->toBe(['The name "Class" is reserved by PHP.'])
        ->and($index.'/site/templates/components/class.blade.php')->not->toBeFile();
});

it('overwrites an existing component with the force option', function () {
    $index = temporarySite();
    App(roots: ['index' => $index]);
    file_put_contents($index.'/app/View/Components/Orphan.php', '<?php // existing');
    file_put_contents($index.'/site/templates/components/orphan.blade.php', 'existing');

    (new MakeComponentCommand)($cli = cli(['name' => 'Orphan', 'force' => true]));

    expect(file_get_contents($index.'/app/View/Components/Orphan.php'))->toContain('class Orphan')
        ->and(file_get_contents($index.'/site/templates/components/orphan.blade.php'))->toContain('components.orphan.blade.php')
        ->and($cli->output)->toHaveCount(2);
});

it('declares the force option for the make component command', function () {
    $commands = require dirname(__DIR__, 2).'/extensions/commands.php';

    expect($commands['make:component']['args'])->toHaveKey('force');
});

it('explains when the composer file for component classes is missing', function () {
    $index = temporarySite();
    unlink($index.'/composer.json');
    App(roots: ['index' => $index]);

    (new MakeComponentCommand)(cli(['name' => 'Banner']));
})->throws(RuntimeException::class, 'Unable to detect application namespace');
