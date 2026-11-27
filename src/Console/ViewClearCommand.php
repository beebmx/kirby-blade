<?php

namespace Beebmx\KirbyBlade\Console;

use Beebmx\KirbyBlade\Template;
use Kirby\CLI\CLI;
use Kirby\Filesystem\F;
use RuntimeException;

class ViewClearCommand
{
    /**
     * Execute the console command.
     *
     * @throws RuntimeException
     */
    public function __invoke(CLI $cli): void
    {
        $container = Template::boot()->container();
        $path = $container['config']['view.compiled'];

        if (! $path) {
            throw new RuntimeException('View path not found.');
        }

        $container['view.engine.resolver']
            ->resolve('blade')
            ->forgetCompiledOrNotExpired();

        foreach (glob("{$path}/*") as $view) {
            F::remove($view);
        }

        $cli->out('Compiled views cleared successfully.');
    }
}
