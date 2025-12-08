<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearCacheCommand extends Command
{
    protected $signature = 'cache:clear-all';

    protected $description = 'Очистить весь кэш приложения, включая Redis';

    public function handle(): int
    {
        $this->info('Очистка кэша приложения...');

        Cache::flush();

        $this->info('Кэш успешно очищен!');

        return Command::SUCCESS;
    }
}

