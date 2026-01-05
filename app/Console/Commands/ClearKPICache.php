<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearKPICache extends Command
{
    protected $signature = 'kpi:clear-cache';
    protected $description = 'Clear KPI system cache';

    public function handle()
    {
        $this->info('🗑️  Clearing KPI cache...');

        Cache::flush();

        $this->info('✅ Cache cleared successfully');
    }
}