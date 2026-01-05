<?php

namespace App\Http\Controllers;

use App\Models\TaskAssignment;
use App\Models\Task;
use App\Models\SubTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreTaskAssignmentRequest;
use App\Http\Requests\UpdateTaskAssignmentRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Carbon\Carbon; 

class TaskAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view-taskassign', ['only' => ['index', 'show']]);
        $this->middleware('permission:create-taskassign', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-taskassign', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-taskassign', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the task assignments.
     */
    public function index(Request $request): View
    {
        $query = TaskAssignment::with(['subtask', 'user']);

        // Employee name filter (instead of employee_id)
        if ($request->filled('employee_name')) {
            $employeeName = $request->employee_name;
            $query->whereHas('user', function($q) use ($employeeName) {
                $q->where(function($subQuery) use ($employeeName) {
                    $subQuery->whereRaw("CONCAT(firstName, ' ', lastName) LIKE ?", ["%{$employeeName}%"])
                            ->orWhere('firstName', 'LIKE', "%{$employeeName}%")
                            ->orWhere('lastName', 'LIKE', "%{$employeeName}%");
                });
            });
        }

        // Date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            
            $query->whereBetween('addDate', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        }

        // Monthly report filter
        if ($request->filled('month_filter')) {
            $monthData = $this->getMonthDateRange($request->month_filter);
            if ($monthData) {
                $query->whereBetween('addDate', [$monthData['start'], $monthData['end']]);
            }
        }

        $assignments = $query->orderBy('addDate', 'desc')->get();

        // Get only users with 'User' role for autocomplete
        $employees = User::select('id', 'firstName', 'lastName')
                        ->whereHas('roles', function($query) {
                            $query->where('name', 'User');
                        })
                        ->orderBy('firstName')
                        ->get();

        return view('admin.task_assignments.index', [
            'assignments' => $assignments,
            'employees' => $employees,
            'filters' => $request->only(['employee_name', 'start_date', 'end_date', 'month_filter'])
        ]);
    }

    private function getMonthDateRange($monthKey)
    {
        $currentDate = Carbon::now();
        
        switch($monthKey) {
            case 'january':
                $start = $currentDate->copy()->month(12)->subYear()->day(26);
                $end = $currentDate->copy()->month(1)->day(25);
                break;
            case 'february':
                $start = $currentDate->copy()->month(1)->day(26);
                $end = $currentDate->copy()->month(2)->day(25);
                break;
            case 'march':
                $start = $currentDate->copy()->month(2)->day(26);
                $end = $currentDate->copy()->month(3)->day(25);
                break;
            case 'april':
                $start = $currentDate->copy()->month(3)->day(26);
                $end = $currentDate->copy()->month(4)->day(25);
                break;
            case 'may':
                $start = $currentDate->copy()->month(4)->day(26);
                $end = $currentDate->copy()->month(5)->day(25);
                break;
            case 'june':
                $start = $currentDate->copy()->month(5)->day(26);
                $end = $currentDate->copy()->month(6)->day(25);
                break;
            case 'july':
                $start = $currentDate->copy()->month(6)->day(26);
                $end = $currentDate->copy()->month(7)->day(25);
                break;
            case 'august':
                $start = $currentDate->copy()->month(7)->day(26);
                $end = $currentDate->copy()->month(8)->day(25);
                break;
            case 'september':
                $start = $currentDate->copy()->month(8)->day(26);
                $end = $currentDate->copy()->month(9)->day(25);
                break;
            case 'october':
                $start = $currentDate->copy()->month(9)->day(26);
                $end = $currentDate->copy()->month(10)->day(25);
                break;
            case 'november':
                $start = $currentDate->copy()->month(10)->day(26);
                $end = $currentDate->copy()->month(11)->day(25);
                break;
            case 'december':
                $start = $currentDate->copy()->month(11)->day(26);
                $end = $currentDate->copy()->month(12)->day(25);
                break;
            default:
                return null;
        }

        // Agar start sana kelajakda bo'lsa, bir yil orqaga suramiz
        if ($start->isFuture()) {
            $start->subYear();
            $end->subYear();
        }

        return [
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d')
        ];
    }

    /**
     * Show the form for creating a new task assignment.
     */
    public function create(): View
    {
        return view('admin.task_assignments.create', [
            // 'tasks' => Task::all(),
            'subtasks' => SubTask::all(),
            'users' => User::all()
        ]);
    }

    /**
     * Store a newly created task assignment in storage.
     */
    public function store(StoreTaskAssignmentRequest $request): RedirectResponse
{
    $data = $request->validated();
    
    // Xodimning hozirgi projectini avtomatik qo'shish
    if (isset($data['user_id'])) {
        $user = User::find($data['user_id']);
        $data['project_id'] = $user ? $user->project_id : null;
    }
    
    TaskAssignment::create($data);

    return redirect()->route('admin.task_assignments.index')
                     ->withSuccess('Tayinlov muvaffaqiyatli qo\'shildi.');
}

    /**
     * Show the form for editing the specified task assignment.
     */
    public function edit(TaskAssignment $taskAssignment): View
    {
        return view('admin.task_assignments.edit', [
            'taskAssignment' => $taskAssignment,
            // 'tasks' => Task::all(),
            'subtasks' => SubTask::all(),
            'users' => User::all()
        ]);
    }

    /**
     * Update the specified task assignment in storage.
     */
    public function update(UpdateTaskAssignmentRequest $request, TaskAssignment $taskAssignment): RedirectResponse
    {
        $taskAssignment->update($request->validated());

        return redirect()->route('admin.task_assignments.index')
                         ->withSuccess('Tayinlov muvaffaqiyatli yangilandi.');
    }

    /**
     * Remove the specified task assignment from storage.
     */
    public function destroy(TaskAssignment $taskAssignment): RedirectResponse
    {
        $taskAssignment->delete();

        return redirect()->route('admin.task_assignments.index')
                         ->withSuccess('Tayinlov muvaffaqiyatli o‘chirildi.');
    }

	public function massDelete(Request $request)
    {
        $ids = $request->input('ids'); // checkbox orqali yuborilayotgan IDlar
    
        if (!empty($ids)) {
            DB::table('task_assignments')->whereIn('id', $ids)->delete();
            return redirect()->back()->with('success', 'Tanlangan topshiriqlar o‘chirildi.');
        }
    
        return redirect()->back()->with('error', 'Hech qanday topshiriq tanlanmadi.');
    }
}
