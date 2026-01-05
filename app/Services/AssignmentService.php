<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\AssignmentRepository;
use Illuminate\Support\Collection;

class AssignmentService
{
    private UserRepository $userRepo;
    private AssignmentRepository $assignmentRepo;
    private BonusCalculatorService $bonusCalculator;
    private KPICalculatorService $kpiCalculator;

    public function __construct(
        UserRepository $userRepo, 
        AssignmentRepository $assignmentRepo, 
        BonusCalculatorService $bonusCalculator, 
        KPICalculatorService $kpiCalculator
    ) {
        $this->userRepo = $userRepo;
        $this->assignmentRepo = $assignmentRepo;
        $this->bonusCalculator = $bonusCalculator;
        $this->kpiCalculator = $kpiCalculator;
    }

    public function prepareSwodData($from, $to, ?string $position = null, bool $withTasks = false): array
    {
        // 1. Base users
        $baseUsers = $this->userRepo->getBaseUsers($from);
        $baseUserIds = $baseUsers->pluck('id')->toArray();

        // 2. Eligible users
        $eligibleUsers = $this->userRepo->getEligibleUsers($baseUsers, $from);

        // 3. Rating aggregatsiya
        $userRatings = $this->assignmentRepo->getUserRatingsOptimized($baseUserIds, $from, $to);

        // 4. Yangi userlar
        $newUsersWithRating = $this->userRepo->getNewUsersWithRating($baseUsers, $from, $userRatings);

        // 5. Global average
        $globalAvg = $this->bonusCalculator->calculateGlobalAverage($eligibleUsers, $newUsersWithRating, $userRatings);

        // 6. Assignments
        $rawAssignments = $withTasks 
            ? $this->assignmentRepo->getMergedAssignments($baseUserIds, $from, $to) 
            : collect();

        // 7. Dominant projects
        $dominantProjects = $withTasks 
            ? $this->bonusCalculator->getDominantProjects($baseUsers, $rawAssignments) 
            : $this->getDefaultDominantProjects($baseUsers);

        // 8. EFFECTIVE PROJECTS - to'g'rilangan!
        $effectiveProjects = $this->bonusCalculator->getEffectiveProjectIds($baseUsers, $dominantProjects, $from, $to);

        // 9. Project bonuses - EFFECTIVE PROJECTS bilan
        $projectBonuses = $this->bonusCalculator->calculateProjectBonuses(
            $baseUsers, 
            $eligibleUsers, 
            $newUsersWithRating, 
            $userRatings, 
            $globalAvg, 
            $effectiveProjects
        );

        // 10. User totals with bonus
        $userTotalsWithBonus = [];
        foreach ($baseUserIds as $userId) {
            $rating = $userRatings[$userId] ?? 0;
            $bonus = $this->bonusCalculator->calculateUserBonus(
                $userId, 
                $rating, 
                $eligibleUsers, 
                $newUsersWithRating, 
                $projectBonuses, 
                $effectiveProjects
            );
            $userTotalsWithBonus[$userId] = $rating + $bonus;
        }

        // 11. KPI
        $kpis = $this->kpiCalculator->calculateAllKPIs($userTotalsWithBonus);
        $maxTotal = $this->kpiCalculator->calculateMaxTotal($userTotalsWithBonus);

        // 12. Position filter
        $displayUsers = $this->userRepo->getUsersByPosition($baseUsers, $position);

        // 13. Final assignments
        $assignments = $displayUsers->mapWithKeys(function ($user) use (
            $userRatings,
            $userTotalsWithBonus,
            $kpis,
            $rawAssignments,
            $globalAvg,
            $eligibleUsers,
            $newUsersWithRating,
            $projectBonuses,
            $dominantProjects,
            $effectiveProjects,
            $withTasks,
            $baseUsers,
            $from,
            $to,
        ) {
            $userId = $user->id;
            $rating = $userRatings[$userId] ?? 0;
            $totalWithBonus = $userTotalsWithBonus[$userId] ?? 0;
            $kpi = $kpis[$userId] ?? 0;
            $bonus = $totalWithBonus - $rating;

            // Project info
            $dominantProjectId = $dominantProjects[$userId] ?? $user->project_id;
            $effectiveProjectId = $effectiveProjects[$userId] ?? $user->project_id;
            $projectBonus = $projectBonuses[$effectiveProjectId] ?? ['project_avg' => 0, 'has_bonus' => false];

            // Project change info
            $projectChangeInfo = $this->bonusCalculator->getProjectChangeInfo(
                $user, $from, $to, $dominantProjectId, $effectiveProjectId
            );

            $data = [
                'user_id' => $userId,
                'globalAvg' => round($globalAvg, 2),
                'projectAvg' => round($projectBonus['project_avg'], 2),
                'total_rating' => $rating,
                'bonus' => $bonus,
                'total_with_bonus' => $totalWithBonus,
                'kpi' => $kpi,
                'was_active' => true,
                'is_eligible_for_bonus' => $eligibleUsers->contains('id', $userId) || $newUsersWithRating->contains('id', $userId),
                'has_bonus' => $projectBonus['has_bonus'],
                'is_deleted' => $user->trashed(),
                'dominant_project_id' => $dominantProjectId,
                'effective_project_id' => $effectiveProjectId,
                'project_change_info' => $projectChangeInfo,
            ];

            // Tasks
            if ($withTasks) {
                $userAssignments = $rawAssignments->where('user_id', $userId);

                $data['tasks'] = $userAssignments
                    ->map(function ($a) use ($baseUsers, $userId) {
                        $projectName = 'N/A';

                        if (isset($a->project) && $a->project) {
                            $projectName = $a->project->name ?? 'N/A';
                        } elseif (isset($a->project_id) && $a->project_id) {
                            $project = \App\Models\Project::withTrashed()->find($a->project_id);
                            $projectName = $project ? $project->name : 'N/A';
                        } else {
                            $userModel = $baseUsers->firstWhere('id', $userId);
                            if ($userModel && $userModel->project) {
                                $projectName = $userModel->project->name;
                            }
                        }

                        return [
                            'task_id' => $a->subtask->task->id ?? null,
                            'task_name' => $a->subtask->task->taskName ?? null,
                            'rating' => $a->rating,
                            'project_name' => $projectName,
                            'add_date' => $a->addDate,
                        ];
                    })
                    ->values()
                    ->toArray();
            }

            return [$userId => $data];
        });

        return [
            'assignments' => $assignments,
            'maxTotalWithBonus' => $maxTotal,
            'globalAvg' => round($globalAvg, 2),
            'staffUsers' => $baseUsers,
        ];
    }

    private function getDefaultDominantProjects(Collection $users): array
    {
        return $users->pluck('project_id', 'id')->toArray();
    }
}