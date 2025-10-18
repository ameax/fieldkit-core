<?php

namespace Ameax\FieldkitCore\Commands;

use Illuminate\Console\Command;

class FieldkitCoreCommand extends Command
{
    public $signature = 'fieldkit-core';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
