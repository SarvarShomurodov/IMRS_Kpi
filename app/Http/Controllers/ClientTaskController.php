<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Bonus;
use App\Models\SubTask;
use App\Models\Assignment;
use Illuminate\Http\Request;
use App\Models\TaskAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TaskAssignmentExport;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use App\Exports\DetailedTaskAssignmentExport;
use App\Services\DateRangeService;
use App\Services\AssignmentService;
use App\Repositories\UserRepository;
use App\Repositories\AssignmentRepository;

class ClientTaskController extends Controller
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
     * ✅ INDEX - Barcha tasksni ko'rsatish
     */
    public function index()
    {
        $tasks = Task::select('id', 'taskName')->get();
        return view('client.tasks.index', compact('tasks'));
    }

    /**
     * ✅ SHOW - Task bo'yicha xodimlar ro'yxati
     */
    public function show($taskId)
    {
        $task = Task::findOrFail($taskId);

        // ✅ Optimized - Faqat kerakli ustunlar
        $staffUsers = User::select('id', 'firstName', 'lastName', 'email', 'position')->orderBy('firstName')->get();

        // ✅ Optimized - Database level aggregation
        $ratings = TaskAssignment::select('user_id', DB::raw('SUM(rating) as total_rating'))->groupBy('user_id')->pluck('total_rating', 'user_id');

        return view('client.tasks.show', compact('task', 'staffUsers', 'ratings'));
    }

    /**
     * ✅ ASSIGN TASK - Baho berish formasi
     */
    public function assignTask($taskId, $userId)
    {
        $task = Task::findOrFail($taskId);
        $staffUser = User::findOrFail($userId);

        $subtasks = SubTask::where('task_id', $taskId)->select('id', 'title', 'min', 'max')->get();

        return view('client.tasks.assign', compact('task', 'staffUser', 'subtasks'));
    }

    /**
     * ✅ STORE RATING - Baho saqlash
     */
    public function storeRating(Request $request, $taskId, $userId)
    {
        $request->validate([
            'subtask_id' => 'required|exists:sub_tasks,id',
            'rate' => 'required|numeric',
            'comment' => 'nullable|string|max:1000',
            'date' => 'required|date',
        ]);

        $task = Task::findOrFail($taskId);
        $staffUser = User::findOrFail($userId);
        $subtask = SubTask::findOrFail($request->subtask_id);

        // Baho chegarasini tekshirish
        if ($request->rate < $subtask->min || $request->rate > $subtask->max) {
            return back()->withErrors([
                'rate' => "Baho {$subtask->min} dan {$subtask->max} gacha bo'lishi kerak!",
            ]);
        }

        TaskAssignment::create([
            'subtask_id' => $request->subtask_id,
            'user_id' => $staffUser->id,
            'project_id' => $staffUser->project_id,
            'rating' => $request->rate,
            'comment' => $request->comment,
            'addDate' => $request->date,
        ]);

        // ✅ Cache tozalash
        Cache::forget('base_users_' . Carbon::parse($request->date)->format('Y-m-d'));

        return redirect()
            ->route('tasks.assign', ['taskId' => $taskId, 'staffId' => $userId])
            ->with('success', 'Baho muvaffaqiyatli saqlandi');
    }

    /**
     * ✅ SWOD - OPTIMIZED
     */
    public function swod(Request $request)
    {
        // Oylarni generatsiya qilish
        $months = [];
        $currentDate = Carbon::now();

        for ($i = 0; $i < 12; $i++) {
            $startDate = $currentDate->copy()->subMonths($i)->day(26)->subMonth();
            $endDate = $currentDate->copy()->subMonths($i)->day(25);

            $months[] = [
                'value' => $startDate->format('Y-m-d') . '|' . $endDate->format('Y-m-d'),
                'label' => $endDate->locale('uz_Latn')->translatedFormat('F Y'),
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ];
        }

        // Agar oy tanlangan bo'lsa
        if ($request->has('month') && $request->input('month')) {
            $selectedMonth = $request->input('month');
            [$fromDate, $toDate] = explode('|', $selectedMonth);
            $from = Carbon::parse($fromDate);
            $to = Carbon::parse($toDate);
        } else {
            // Eski usul - qo'lda sana kiritish
            [$from, $to] = DateRangeService::getDefaultRange($request->input('from_date'), $request->input('to_date'));
            $selectedMonth = null;
        }

        // 2. Position filter
        $position = $request->input('position');

        // 3. ✅ SERVICE - Barcha hisob-kitoblar (tasks bilan)
        $data = $this->assignmentService->prepareSwodData($from, $to, $position, true);

        // 4. Tasks va Positions
        $tasks = Task::select('id', 'taskName')->get();

        $positions = User::whereNotNull('position')->where('position', '!=', '')->distinct()->orderBy('position')->pluck('position');

        return view('client.swod.swod', [
            'tasks' => $tasks,
            'staffUsers' => $data['staffUsers'],
            'assignments' => $data['assignments'],
            'months' => $months,
            'selectedMonth' => $selectedMonth,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'maxTotalWithBonus' => $data['maxTotalWithBonus'],
            'positions' => $positions,
        ]);
    }

    /**
     * ✅ GRAFIK - OPTIMIZED
     */
    public function grafik(Request $request)
    {
        // Oylarni generatsiya qilish
        $months = [];
        $currentDate = Carbon::now();

        for ($i = 0; $i < 12; $i++) {
            $startDate = $currentDate->copy()->subMonths($i)->day(26)->subMonth();
            $endDate = $currentDate->copy()->subMonths($i)->day(25);

            $months[] = [
                'value' => $startDate->format('Y-m-d') . '|' . $endDate->format('Y-m-d'),
                'label' => $endDate->locale('uz_Latn')->translatedFormat('F Y'),
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ];
        }

        // Agar oy tanlangan bo'lsa
        if ($request->has('month') && $request->input('month')) {
            $selectedMonth = $request->input('month');
            [$fromDate, $toDate] = explode('|', $selectedMonth);
            $from = Carbon::parse($fromDate);
            $to = Carbon::parse($toDate);
        } else {
            // Eski usul - qo'lda sana kiritish
            [$from, $to] = DateRangeService::getDefaultRange($request->input('from_date'), $request->input('to_date'));
            $selectedMonth = null;
        }

        $position = $request->input('position');

        // ✅ SERVICE orqali
        $data = $this->assignmentService->prepareSwodData($from, $to, $position, true);

        $tasks = Task::select('id', 'taskName')->get();

        $positions = User::whereNotNull('position')->where('position', '!=', '')->distinct()->orderBy('position')->pluck('position');

        return view('client.swod.index', [
            'tasks' => $tasks,
            'staffUsers' => $data['staffUsers'],
            'assignments' => $data['assignments'],
            'positions' => $positions,
            'months' => $months,
            'selectedMonth' => $selectedMonth,
            'today' => $to->toDateString(),
            'oneMonthAgo' => $from->toDateString(),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    /**
     * ✅ SHOW ASSIGN - OPTIMIZED
     */
    public function showAssign(Request $request, $userId)
    {
        $user = User::withTrashed()->findOrFail($userId);

        // Oylarni generatsiya qilish
        $months = [];
        $currentDate = Carbon::now();

        for ($i = 0; $i < 12; $i++) {
            $startDate = $currentDate->copy()->subMonths($i)->day(26)->subMonth();
            $endDate = $currentDate->copy()->subMonths($i)->day(25);

            $months[] = [
                'value' => $startDate->format('Y-m-d') . '|' . $endDate->format('Y-m-d'),
                'label' => $endDate->locale('uz_Latn')->translatedFormat('F Y'),
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ];
        }

        // Agar oy tanlangan bo'lsa
        if ($request->has('month') && $request->input('month')) {
            $selectedMonth = $request->input('month');
            [$fromDate, $toDate] = explode('|', $selectedMonth);
            $from = Carbon::parse($fromDate);
            $to = Carbon::parse($toDate);
        } else {
            // Eski usul - qo'lda sana kiritish
            [$from, $to] = DateRangeService::getDefaultRange($request->input('from_date'), $request->input('to_date'));
            $selectedMonth = null;
        }

        // ✅ Repository orqali
        $assignments = $this->assignmentRepo->getUserAssignments($userId, $from, $to);

        // Sanaga ko'ra saralash
        $assignments = $assignments->sortByDesc('addDate')->values();

        return view('client.swod.show', [
            'user' => $user,
            'assignments' => $assignments,
            'months' => $months,
            'selectedMonth' => $selectedMonth,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'isDeleted' => $user->trashed(),
        ]);
    }

    /**
     * ✅ EXPORT EXCEL - OPTIMIZED
     */
    public function exportExcel(Request $request)
    {
        [$from, $to] = DateRangeService::getDefaultRange($request->get('start_date'), $request->get('end_date'));

        $fromDate = $from->toDateString();
        $toDate = $to->toDateString();

        $fileName = "task_assignments_by_users_{$fromDate}_to_{$toDate}.xlsx";

        return Excel::download(new TaskAssignmentExport($fromDate, $toDate), $fileName);
    }

    /**
     * ✅ EXPORT DETAILED EXCEL
     */
    public function exportDetailedExcel(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $fileName = 'detailed_task_assignments_' . ($startDate ?? 'auto') . '_to_' . ($endDate ?? 'auto') . '.xlsx';

        return Excel::download(new DetailedTaskAssignmentExport($startDate, $endDate), $fileName);
    }

    /**
     * ✅ TASK DETAILS
     */
    public function taskDetails($userId, $taskId, Request $request)
    {
        $user = User::findOrFail($userId);
        $task = Task::findOrFail($taskId);

        [$from, $to] = DateRangeService::getDefaultRange($request->input('from_date'), $request->input('to_date'));

        // ✅ Optimized query
        $assignments = TaskAssignment::with('subtask:id,title,min,max,task_id')
            ->where('user_id', $userId)
            ->whereHas('subtask', function ($q) use ($taskId) {
                $q->where('task_id', $taskId);
            })
            ->whereBetween('addDate', [$from, $to])
            ->select(['id', 'subtask_id', 'user_id', 'rating', 'comment', 'addDate'])
            ->orderBy('addDate', 'desc')
            ->get();

        return view('client.swod.task-details', [
            'user' => $user,
            'task' => $task,
            'assignments' => $assignments,
            'from' => $from, // ✅ Carbon object (toDateString() ni o'chirdik)
            'to' => $to, // ✅ Carbon object
        ]);
    }

    /**
     * ✅ STAFF - Barcha xodimlar (LOYIHA BILAN)
     */
    public function staff()
    {
        $staffUsers = User::withTrashed()
            ->with('project:id,name') // ✅ Loyihani eager load qilish
            ->select('id', 'firstName', 'lastName', 'email', 'position', 'project_id', 'phone', 'deleted_at') // ✅ project_id va phone qo'shildi
            ->orderBy('firstName')
            ->get();

        return view('client.tasks.staff', compact('staffUsers'));
    }

    /**
     * ✅ KPI - Yillik user KPI (12 oy)
     */
    public function kpi(User $user)
    {
        $year = 2025;
        $tasks = Task::select('id', 'taskName')->get();

        // ✅ Base users (normal users)
        $normalUsers = $this->userRepo->getBaseUsers(Carbon::now());

        $kpiResults = collect();

        for ($month = 1; $month <= 12; $month++) {
            [$from, $to] = DateRangeService::getRangeByYearMonth($year, $month);
            $kpiMonthName = Carbon::create($year, $month, 1)->format('F');

            // ✅ SERVICE orqali hisoblash
            $data = $this->assignmentService->prepareSwodData($from, $to, null, true);

            // Hozirgi user uchun ma'lumot
            $userAssignment = $data['assignments']->get($user->id);

            if (!$userAssignment) {
                $kpiResults->push([
                    'month' => $kpiMonthName,
                    'task_ratings' => [],
                    'kpi' => 0,
                    'total_rating' => 0,
                    'bonus' => 0,
                    'total_with_bonus' => 0,
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                ]);
                continue;
            }

            // Tasklar bo'yicha rating
            $taskRatings = collect($userAssignment['tasks'] ?? [])
                ->groupBy('task_name')
                ->map(fn($group) => $group->sum('rating'))
                ->toArray();

            $kpiResults->push([
                'month' => $kpiMonthName,
                'task_ratings' => $taskRatings,
                'kpi' => $userAssignment['kpi'],
                'total_rating' => $userAssignment['total_rating'],
                'bonus' => $userAssignment['bonus'],
                'total_with_bonus' => $userAssignment['total_with_bonus'],
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ]);
        }

        return view('client.tasks.grafikstaff', [
            'user' => $user,
            'kpiResults' => $kpiResults,
        ]);
    }

    /**
     * ✅ USER ASSIGNMENTS - Xodim assignmentlari
     */
    public function userAssignments($id, Request $request)
    {
        $staffUser = User::withTrashed()->findOrFail($id);

        // ✅ Optimized query
        $query = Assignment::where('user_id', $id)
            ->with(['task:id,taskName', 'subtask:id,title,min,max,task_id'])
            ->select(['id', 'user_id', 'task_id', 'subtask_id', 'rating', 'name', 'who_from', 'date', 'who_hand', 'people', 'file', 'comment', 'created_at'])
            ->orderBy('date', 'desc');

        if (!$request->has('show_all')) {
            $query->whereNull('comment');
        }

        // ✅ Count optimizatsiya
        $nullCommentCount = Assignment::where('user_id', $id)->whereNull('comment')->count();

        if (!$request->has('show_all') && $nullCommentCount <= 20) {
            $assignments = $query->get();
            $showPagination = false;
        } else {
            $assignments = $query->paginate(20);
            $showPagination = true;
        }

        // ✅ Cache - Tasks
        $tasks = Cache::remember('tasks_without_11', 3600, function () {
            return Task::select('id', 'taskName')->where('id', '!=', 11)->orderBy('taskName')->get();
        });

        $task11 = Cache::remember('task_11', 3600, function () {
            return Task::select('id', 'taskName')->find(11);
        });

        // 11-task ruxsati
        $assignmentsWithTask11 = Assignment::where('user_id', $id)->where('task_id', 11)->pluck('id')->toArray();

        return view('admin.assignments.index', compact('assignments', 'staffUser', 'showPagination', 'tasks', 'task11', 'assignmentsWithTask11'));
    }

    /**
     * ✅ GET SUBTASKS - AJAX
     */
    public function getSubtasks($taskId)
    {
        // ✅ Cache
        $subtasks = Cache::remember("task_{$taskId}_subtasks", 3600, function () use ($taskId) {
            return SubTask::where('task_id', $taskId)->select('id', 'title', 'min', 'max')->orderBy('title')->get();
        });

        return response()->json($subtasks);
    }

    /**
     * ✅ SAVE ASSIGNMENT RATING
     */
    public function saveAssignmentRating(Request $request, $id)
    {
        $request->validate([
            'task_id' => 'nullable|exists:tasks,id',
            'subtask_id' => 'nullable|exists:sub_tasks,id',
            'rating' => 'nullable|numeric',
            'custom_comment' => 'nullable|string|max:500',
            'action' => 'required|in:accept,reject',
        ]);

        DB::beginTransaction();

        try {
            $assignment = Assignment::findOrFail($id);

            if ($request->action === 'accept') {
                if (!$request->task_id || !$request->subtask_id || !$request->rating) {
                    return back()->withErrors(['error' => 'Task, Subtask va Baho majburiy!']);
                }

                $subtask = SubTask::findOrFail($request->subtask_id);

                // Baho chegarasi
                if ($request->rating < $subtask->min || $request->rating > $subtask->max) {
                    return back()->withErrors([
                        'error' => "Baho {$subtask->min} dan {$subtask->max} gacha bo'lishi kerak!",
                    ]);
                }

                $assignment->task_id = $request->task_id;
                $assignment->subtask_id = $request->subtask_id;
                $assignment->rating = $request->rating;

                $commentText = $subtask->title . ' baholandi';
                if ($request->custom_comment) {
                    $commentText .= ' | ' . $request->custom_comment;
                }
                $assignment->comment = $commentText;
            } else {
                // Rad etish
                $assignment->task_id = null;
                $assignment->subtask_id = null;
                $assignment->rating = null;

                $commentText = 'Baholanmaydi / Не оценивается';
                if ($request->custom_comment) {
                    $commentText .= ' | Sabab: ' . $request->custom_comment;
                }
                $assignment->comment = $commentText;
            }

            $assignment->save();

            // Cache tozalash
            Cache::forget('all_tasks_list');
            Cache::forget("task_{$request->task_id}_subtasks");
            $this->userRepo->clearCache();

            DB::commit();

            return redirect()->route('assignment.user', $assignment->user_id)->with('success', 'Baho saqlandi!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Xatolik: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ EDIT ASSIGNMENT
     */
    public function edit($id)
    {
        $assignment = Assignment::findOrFail($id);
        return view('admin.assignments.edit', compact('assignment'));
    }

    /**
     * ✅ UPDATE COMMENT
     */
    public function updateComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'nullable|string|max:2000',
        ]);

        $assignment = Assignment::findOrFail($id);
        $assignment->comment = $request->comment;
        $assignment->save();

        return redirect()->route('assignment.user', $assignment->user_id)->with('success', 'Comment saqlandi');
    }

    /**
     * ✅ DELETE RATING
     */
    public function deleteRating($id)
    {
        try {
            $assignment = Assignment::findOrFail($id);

            $assignment->task_id = null;
            $assignment->subtask_id = null;
            $assignment->rating = null;
            $assignment->comment = null;
            $assignment->save();

            // Cache tozalash
            $this->userRepo->clearCache();

            return redirect()->back()->with('success', 'Baholash o\'chirildi!');
        } catch (\Exception $e) {
            \Log::error('deleteRating error: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Xatolik: ' . $e->getMessage());
        }
    }

    /**
     * ✅ USER PENALTIES - Jarimalar sahifasi
     */
    public function userPenalties($id, Request $request)
    {
        $staffUser = User::withTrashed()->findOrFail($id);

        // Jarima taski
        $penaltyTask = Task::where(function ($query) {
            $query->where('taskName', 'LIKE', '%Жарима%')->orWhere('taskName', 'LIKE', '%Штраф%')->orWhere('taskName', 'LIKE', '%Jarima%');
        })->first();

        if (!$penaltyTask) {
            return back()->withErrors(['error' => 'Jarima taski topilmadi!']);
        }

        $penaltySubtasks = SubTask::where('task_id', $penaltyTask->id)->select('id', 'title', 'min', 'max')->orderBy('title')->get();

        // Jarimalar
        $query = TaskAssignment::with(['subtask:id,title,min,max'])
            ->where('user_id', $id)
            ->whereHas('subtask', function ($q) use ($penaltyTask) {
                $q->where('task_id', $penaltyTask->id);
            })
            ->select(['id', 'user_id', 'subtask_id', 'rating', 'comment', 'addDate', 'created_at'])
            ->orderBy('addDate', 'desc');

        // Date filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('addDate', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('addDate', '<=', $request->date_to);
        }

        $penalties = $query->paginate(20);

        // Jami jarima
        $totalPenaltyScore = TaskAssignment::where('user_id', $id)
            ->whereHas('subtask', function ($q) use ($penaltyTask) {
                $q->where('task_id', $penaltyTask->id);
            })
            ->sum('rating');

        return view('admin.assignments.penalties', compact('staffUser', 'penaltyTask', 'penaltySubtasks', 'penalties', 'totalPenaltyScore'));
    }

    /**
     * ✅ STORE USER PENALTY
     */
    public function storeUserPenalty(Request $request, $id)
    {
        $staffUser = User::findOrFail($id);

        $request->validate([
            'subtask_id' => 'required|exists:sub_tasks,id',
            'rating' => 'required|numeric',
            'comment' => 'nullable|string|max:1000',
            'addDate' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $subtask = SubTask::findOrFail($request->subtask_id);

            if ($request->rating < $subtask->min || $request->rating > $subtask->max) {
                return back()->withErrors([
                    'error' => "Jarima {$subtask->min} dan {$subtask->max} gacha!",
                ]);
            }

            TaskAssignment::create([
                'user_id' => $id,
                'subtask_id' => $request->subtask_id,
                'rating' => $request->rating,
                'comment' => $request->comment ?? $subtask->title,
                'addDate' => $request->addDate,
            ]);

            // Cache tozalash
            $this->userRepo->clearCache();

            DB::commit();

            return redirect()->route('assignment.user.penalties', $id)->with('success', 'Jarima saqlandi!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Xatolik: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ UPDATE USER PENALTY
     */
    public function updateUserPenalty(Request $request, $id, $penaltyId)
    {
        $staffUser = User::findOrFail($id);

        $request->validate([
            'subtask_id' => 'required|exists:sub_tasks,id',
            'rating' => 'required|numeric',
            'comment' => 'nullable|string|max:1000',
            'addDate' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $penalty = TaskAssignment::where('id', $penaltyId)->where('user_id', $id)->firstOrFail();

            $subtask = SubTask::findOrFail($request->subtask_id);

            if ($request->rating < $subtask->min || $request->rating > $subtask->max) {
                return back()->withErrors([
                    'error' => "Jarima {$subtask->min} dan {$subtask->max} gacha!",
                ]);
            }

            $penalty->update([
                'subtask_id' => $request->subtask_id,
                'rating' => $request->rating,
                'comment' => $request->comment ?? $subtask->title,
                'addDate' => $request->addDate,
            ]);

            // Cache tozalash
            $this->userRepo->clearCache();

            DB::commit();

            return redirect()->route('assignment.user.penalties', $id)->with('success', 'Jarima yangilandi!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Xatolik: ' . $e->getMessage()]);
        }
    }

    /**
     * ✅ DELETE USER PENALTY
     */
    public function deleteUserPenalty($id, $penaltyId)
    {
        try {
            $penalty = TaskAssignment::where('id', $penaltyId)->where('user_id', $id)->firstOrFail();

            $penalty->delete();

            // Cache tozalash
            $this->userRepo->clearCache();

            return redirect()->route('assignment.user.penalties', $id)->with('success', 'Jarima o\'chirildi!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Xatolik: ' . $e->getMessage()]);
        }
    }
}
