<?php

namespace Akira\LaravelLicense\Commands;

use Illuminate\Console\Command;

class LaravelLicenseCommand extends Command
{
    public $signature = 'laravel-license';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
