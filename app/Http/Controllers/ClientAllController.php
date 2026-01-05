<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Models\Assignment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TaskAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\DateRangeService;
use App\Services\AssignmentService;
use App\Repositories\UserRepository;
use App\Repositories\AssignmentRepository;

class ClientAllController extends Controller
{
    private AssignmentService $assignmentService;
    private UserRepository $userRepo;
    private AssignmentRepository $assignmentRepo;

    public function __construct(AssignmentService $assignmentService, UserRepository $userRepo, AssignmentRepository $assignmentRepo)
    {
        $this->assignmentService = $assignmentService;
        $this->userRepo = $userRepo;
        $this->assignmentRepo = $assignmentRepo;
    }

    /**
     * ✅ INDEX - Auth user dashboard (TASK KARTOCHKALARI BILAN)
     */
    public function index(Request $request)
    {
        $year = $request->input('year') ?? Carbon::now()->year;
        $month = $request->input('month') ?? Carbon::now()->month;

        [$from, $to] = DateRangeService::getRangeByYearMonth($year, $month);

        $authUser = auth()->user();
        $authUserId = $authUser->id;

        // ✅ withTasks = TRUE - Tasklar bilan
        $data = $this->assignmentService->prepareSwodData($from, $to, null, true);

        // Faqat auth user uchun
        $authUserData = $data['assignments']->get($authUserId);

        if (!$authUserData) {
            // ✅ Bo'sh bo'lsa, default ma'lumotlar
            $authUserData = [
                'user_id' => $authUserId,
                'globalAvg' => $data['globalAvg'],
                'projectAvg' => 0,
                'total_rating' => 0,
                'bonus' => 0,
                'total_with_bonus' => 0,
                'kpi' => 0,
                'was_active' => false,
                'is_eligible_for_bonus' => false,
                'has_bonus' => false,
                'is_deleted' => false,
                'tasks' => [], // ✅ Bo'sh tasks array
            ];
        }

        // ✅ Tasks mavjudligini tekshirish
        if (!isset($authUserData['tasks'])) {
            $authUserData['tasks'] = [];
        }

        $assignments = collect([$authUserId => $authUserData]);

        // ✅ 12 oy uchun totalWithBonus
        $totalWithBonusByMonth = $this->calculateMonthlyTotals($authUserId, $year);

        $tasks = Task::select('id', 'taskName')->get();
        $staffUsers = User::with('project:id,name')->select('id', 'firstName', 'lastName', 'project_id')->get();

        return view('client.view.test', [
            'tasks' => $tasks,
            'staffUsers' => $staffUsers,
            'assignments' => $assignments,
            'fromDate' => $from->toDateString(),
            'toDate' => $to->toDateString(),
            'totalWithBonusByMonth' => $totalWithBonusByMonth,
            'year' => $year,
            'month' => $month,
            'globalAvg' => $data['globalAvg'],
        ]);
    }

    /**
     * ✅ SUBTASK - Auth user subtask statistikasi
     */
    public function subtask(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $year = $request->input('year') ?? Carbon::now()->year;
        $month = $request->input('month') ?? 1;

        [$from, $to] = DateRangeService::getRangeByYearMonth($year, $month);

        $fromDate = $from->toDateString();
        $toDate = $to->toDateString();

        // ✅ Repository orqali faqat shu user uchun
        $assignments = $this->assignmentRepo->getUserAssignments($user->id, $from, $to);

        // Task bo'yicha guruhlash
        $userAssignment = $assignments->groupBy(function ($item) {
            return $item->subtask->task->taskName ?? 'Noma\'lum';
        });

        $taskStats = $userAssignment->map(function ($group) {
            return [
                'sum' => $group->sum('rating'),
                'avg' => round($group->avg('rating'), 2),
                'assignments' => $group,
            ];
        });

        $totalSum = $assignments->sum('rating');
        $totalAvg = round($assignments->avg('rating'), 2);
        $totalCount = $assignments->count();

        // ✅ Bonus hisoblash - SERVICE orqali
        $data = $this->assignmentService->prepareSwodData($from, $to, null, false);
        $userData = $data['assignments']->get($user->id);

        $bonus = $userData['bonus'] ?? 0;
        $totalWithBonus = $userData['total_with_bonus'] ?? $totalSum;
        $projectAvg = $userData['projectAvg'] ?? 0;

        return view('client.view.dataset', [
            'user' => $user,
            'taskStats' => $taskStats,
            'totalSum' => $totalSum,
            'totalAvg' => $totalAvg,
            'totalCount' => $totalCount,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'bonus' => $bonus,
            'totalWithBonus' => $totalWithBonus,
            'projectAvg' => $projectAvg,
            'globalAvg' => $data['globalAvg'],
        ]);
    }

    /**
     * ✅ ALL SUBTASK - Barcha userlar statistikasi
     */
    public function allsubtask(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $year = $request->input('year') ?? Carbon::now()->year;
        $month = $request->input('month') ?? 1;

        [$from, $to] = DateRangeService::getRangeByYearMonth($year, $month);

        $fromDate = $from->toDateString();
        $toDate = $to->toDateString();

        // ✅ SERVICE orqali barcha ma'lumotlar (tasks bilan)
        $data = $this->assignmentService->prepareSwodData($from, $to, null, true);

        $tasks = Task::select('id', 'taskName')->get();

        $positions = User::whereNotNull('position')->where('position', '!=', '')->distinct()->orderBy('position')->pluck('position');

        $userPosition = $user->position;

        $samePositionUsers = User::where('position', $userPosition)->with('project:id,name')->select('id', 'firstName', 'lastName', 'position', 'project_id', 'deleted_at')->get();

        // Auth user bonusi
        $authUserData = $data['assignments']->get($user->id);
        $bonus = $authUserData['bonus'] ?? 0;

        // ✅ Frontend uchun format
        $filteredStaffUsers = $data['staffUsers']
            ->map(
                fn($u) => [
                    'id' => $u->id,
                    'firstName' => $u->firstName,
                    'lastName' => $u->lastName,
                    'roles' => $u->roles->pluck('name')->toArray(),
                    'is_deleted' => $u->trashed(),
                ],
            )
            ->values()
            ->toArray();

        $filteredSamePositionUsers = $samePositionUsers
            ->map(
                fn($u) => [
                    'id' => $u->id,
                    'firstName' => $u->firstName,
                    'lastName' => $u->lastName,
                    'is_deleted' => $u->trashed(),
                ],
            )
            ->values()
            ->toArray();

        return view('client.view.userstats', [
            'tasks' => $tasks,
            'filteredStaffUsers' => $filteredStaffUsers,
            'allUsersWithTexnik' => $filteredStaffUsers, // Bir xil
            'filteredSamePositionUsers' => $filteredSamePositionUsers,
            'assignments' => $data['assignments'],
            'positions' => $positions,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'userPosition' => $userPosition,
            'bonus' => $bonus,
        ]);
    }

    /**
     * ✅ EDIT PROFILE
     */
    public function editProfile()
    {
        return view('client.view.profile', ['user' => auth()->user()]);
    }

    /**
     * ✅ UPDATE PROFILE
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
            'old_password' => 'required',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        if (!Hash::check($request->old_password, $user->password)) {
            return back()->withErrors(['old_password' => 'Eski parol noto\'g\'ri']);
        }

        $user->email = $request->email;

        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return back()->with('success', 'Profil yangilandi!');
    }

    /**
     * ✅ PRIVATE - 12 oy uchun totalWithBonus hisoblash
     */
    private function calculateMonthlyTotals(int $userId, int $year): \Illuminate\Support\Collection
    {
        $totals = collect();

        for ($month = 1; $month <= 12; $month++) {
            [$from, $to] = DateRangeService::getRangeByYearMonth($year, $month);

            // ✅ Optimized - Database aggregation
            $oldRating = DB::table('task_assignments')
                ->where('user_id', $userId)
                ->whereBetween('addDate', [$from, $to])
                ->sum('rating');

            $newRating = DB::table('assignments')
                ->where('user_id', $userId)
                ->whereNotNull('task_id')
                ->whereNotNull('subtask_id')
                ->whereNotNull('rating')
                ->whereBetween('date', [$from, $to])
                ->sum('rating');

            $totals->put($month, $oldRating + $newRating);
        }

        return $totals;
    }
}
