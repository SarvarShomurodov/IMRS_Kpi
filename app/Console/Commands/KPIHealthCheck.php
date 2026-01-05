<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class KPIHealthCheck extends Command
{
    protected $signature = 'kpi:health';
    protected $description = 'KPI System Health Check';

    public function handle()
    {
        $this->info('🔍 Starting KPI Health Check...');
        $this->newLine();

        // Database
        try {
            DB::connection()->getPdo();
            $this->info('✅ Database: Connected');
        } catch (\Exception $e) {
            $this->error('❌ Database: ' . $e->getMessage());
        }

        // Cache
        try {
            Cache::put('health_check', 'ok', 10);
            $this->info('✅ Cache: Working');
        } catch (\Exception $e) {
            $this->error('❌ Cache: ' . $e->getMessage());
        }

        // Data count
        $oldCount = DB::table('task_assignments')->count();
        $newCount = DB::table('assignments')->count();
        $usersCount = DB::table('users')->count();

        $this->newLine();
        $this->info('📊 Statistics:');
        $this->line("   Old Assignments: " . number_format($oldCount));
        $this->line("   New Assignments: " . number_format($newCount));
        $this->line("   Total Assignments: " . number_format($oldCount + $newCount));
        $this->line("   Users: " . number_format($usersCount));

        $this->newLine();
        $this->info('✅ Health Check Completed');
    }
}