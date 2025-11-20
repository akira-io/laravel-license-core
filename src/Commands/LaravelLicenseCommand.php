<?php

declare(strict_types=1);

namespace Akira\LaravelLicense\Commands;

use Illuminate\Console\Command;

final class LaravelLicenseCommand extends Command
{
    public $signature = 'laravel-license';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
