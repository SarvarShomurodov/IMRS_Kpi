<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AssignmentService;
use App\Services\DateRangeService;
use Carbon\Carbon;

class ValidateKPICommand extends Command
{
    protected $signature = 'kpi:validate {--month=} {--year=}';
    protected $description = 'Validate KPI calculations for specific period';

    private AssignmentService $assignmentService;

    public function __construct(AssignmentService $assignmentService)
    {
        parent::__construct();
        $this->assignmentService = $assignmentService;
    }

    public function handle()
    {
        $year = $this->option('year') ?? Carbon::now()->year;
        $month = $this->option('month') ?? Carbon::now()->month;

        [$from, $to] = DateRangeService::getRangeByYearMonth($year, $month);

        $this->info("Validating KPI for {$from->format('Y-m-d')} to {$to->format('Y-m-d')}");

        $data = $this->assignmentService->prepareSwodData($from, $to);

        $errors = 0;
        $warnings = 0;

        foreach ($data['assignments'] as $userId => $assignment) {
            // ✅ Validation checks
            $expectedTotal = $assignment['total_rating'] + $assignment['bonus'];
            
            if (abs($expectedTotal - $assignment['total_with_bonus']) > 0.01) {
                $this->error("❌ User {$userId}: Total mismatch");
                $errors++;
            }

            if ($assignment['kpi'] < 0 || $assignment['kpi'] > 100) {
                $this->error("❌ User {$userId}: KPI out of range");
                $errors++;
            }

            if ($assignment['bonus'] < 0) {
                $this->error("❌ User {$userId}: Negative bonus");
                $errors++;
            }

            if ($assignment['total_rating'] > 0 && $assignment['kpi'] == 0) {
                $this->warn("⚠️  User {$userId}: Has rating but KPI is 0");
                $warnings++;
            }
        }

        if ($errors == 0 && $warnings == 0) {
            $this->info("✅ All KPI calculations are valid!");
        } else {
            $this->info("\nSummary:");
            $this->info("Errors: {$errors}");
            $this->info("Warnings: {$warnings}");
        }

        return $errors > 0 ? 1 : 0;
    }
}