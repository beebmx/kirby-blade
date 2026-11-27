<?php

it('reports the plugin version to kirby', function () {
    $composer = json_decode(file_get_contents(dirname(__DIR__, 2).'/composer.json'), true);

    expect($composer['version'] ?? null)->toMatch('/^\d+\.\d+\.\d+/')
        ->and(App()->plugin('beebmx/kirby-blade')->version())->toBe($composer['version']);
});
