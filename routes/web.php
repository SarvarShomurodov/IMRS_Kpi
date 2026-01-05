<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BonusController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubTaskController;
use App\Http\Controllers\ClientAllController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClientTaskController;
use App\Http\Controllers\WordExportController;
use App\Http\Controllers\TaskAssignmentController;
use App\Http\Controllers\AssignmentNotificationController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Employee\ReportController as EmployeeReportController;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/{any?}', function () {
//     return view('maintenance');
// })->where('any', '.*')->name('maintenance');

Route::get('/home', function () {
    return view('home');
})->name('home');

Auth::routes(['register' => false]);

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->hasRole('User')) {
            return redirect()->route('client.allsubtask');
        } elseif ($user->hasRole('Admin')) {
            return redirect()->route('grafik');
        } elseif ($user->hasRole('Super Admin')) {
            return redirect()->route('admin.users.index');
        } elseif ($user->hasRole('Texnik')) {
            return redirect()->route('employee.attendance.index');
        }
    }
    return redirect()->route('login');
});

// Super Admin va Admin routes
Route::middleware(['auth', 'role:Super Admin|Admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        Route::resources([
            'roles' => RoleController::class,
            'users' => UserController::class,
            'projects' => ProjectController::class,
            'tasks' => TaskController::class,
            'subtasks' => SubTaskController::class,
            'task_assignments' => TaskAssignmentController::class,
        ]);
        // YANGI SOFT DELETE ROUTES - USERS UCHUN
        Route::get('/users-trashed', [UserController::class, 'trashed'])->name('users.trashed');
        Route::post('/users-restore/{id}', [UserController::class, 'restore'])->name('users.restore');
        Route::delete('/users-force/{id}', [UserController::class, 'forceDelete'])->name('users.force-delete');

        Route::delete('mass-delete', [TaskAssignmentController::class, 'massDelete'])->name('task_assignments.massDelete');

        // Admin uchun davomat nazorati
        Route::get('/attendance', [AttendanceController::class, 'adminIndex'])->name('attendance.index');
        Route::get('/attendance/{user}', [AttendanceController::class, 'adminShow'])->name('attendance.show');
        Route::post('/attendance/{user}', [AttendanceController::class, 'adminStore'])->name('attendance.store');
        Route::get('/attendance-data/{user}/{date}', [AttendanceController::class, 'getAttendanceData'])->name('attendance.data');
        //Admin hisobotlarni boshqarish routelari
        // Hisobotlar - cheklangan access bilan
        Route::middleware('restrict.report.access')->group(function () {
            Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/{report}', [AdminReportController::class, 'show'])->name('reports.show');
            Route::post('/reports/{report}/review', [AdminReportController::class, 'review'])->name('reports.review');
            Route::patch('/reports/{report}/review', [AdminReportController::class, 'updateReview'])->name('reports.update-review');
            Route::get('/reports/analytics/dashboard', [AdminReportController::class, 'analytics'])->name('reports.analytics');
            Route::get('admin/reports/{id}/export', [AdminReportController::class, 'exportSingle'])->name('reports.export-single');
        });
        Route::get('reports/{report}/download/{filename}', [AdminReportController::class, 'downloadAttachment'])
        ->name('reports.download-attachment');
    });

// Admin routes
Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/tasks', [ClientTaskController::class, 'index'])->name('tasks.index');
    Route::get('/task/swod', [ClientTaskController::class, 'swod'])->name('task.swod');
    Route::get('/grafik', [ClientTaskController::class, 'grafik'])->name('grafik');
    Route::get('/accounts/staff/', [ClientTaskController::class, 'staff'])->name('accounts.staffs');
    Route::get('/staff/kpi/{user}', [ClientTaskController::class, 'kpi'])->name('accounts.staff.kpi');
    Route::get('/task-assignments/{user}', [ClientTaskController::class, 'showAssign'])->name('client-task.show');
    Route::get('/taskassignments/export', [ClientTaskController::class, 'exportExcel'])->name('task-assignments.export');
    Route::get('/taskassignments/export-detailed', [ClientTaskController::class, 'exportDetailedExcel'])->name('task-assignments.export-detailed');
    Route::get('/task-assignments/{user}/task/{task}', [ClientTaskController::class, 'taskDetails'])->name('client-task.task-details');
    Route::get('/tasks/{taskId}', [ClientTaskController::class, 'show'])->name('tasks.show');
    Route::get('/tasks/{taskId}/assign/{staffId}', [ClientTaskController::class, 'assignTask'])->name('tasks.assign');
    Route::post('/tasks/{taskId}/assign/{staffId}', [ClientTaskController::class, 'storeRating'])->name('tasks.storeRating');
    Route::get('/assignment/user/{id}', [ClientTaskController::class, 'userAssignments'])->name('assignment.user');
    Route::get('/assignment/{id}/edit', [ClientTaskController::class, 'edit'])->name('assignment.edit');
    Route::post('/assignment/{id}/update-comment', [ClientTaskController::class, 'updateComment'])->name('assignment.updateComment');
    Route::get('/get-subtasks/{taskId}', [ClientTaskController::class, 'getSubtasks'])->name('tasks.getSubtasks');
    Route::post('/assignment/{id}/save-rating', [ClientTaskController::class, 'saveAssignmentRating'])->name('assignment.saveRating');
    // Jarima routes - xodim ID bilan
    Route::get('/assignment/user/{id}/penalties', [ClientTaskController::class, 'userPenalties'])->name('assignment.user.penalties');
    Route::post('/assignment/user/{id}/penalties/store', [ClientTaskController::class, 'storeUserPenalty'])->name('assignment.user.penalties.store');
    Route::put('/assignment/user/{id}/penalties/{penaltyId}/update', [ClientTaskController::class, 'updateUserPenalty'])->name('assignment.user.penalties.update');
    Route::delete('/assignment/user/{id}/penalties/{penaltyId}/delete', [ClientTaskController::class, 'deleteUserPenalty'])->name('assignment.user.penalties.delete');
    // Baholashni o'chirish
    Route::delete('/assignment/{id}/delete-rating', [ClientTaskController::class, 'deleteRating'])->name('assignment.deleteRating');
    Route::get('/notifications', [AssignmentNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [AssignmentNotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::post('/notifications/{id}/mark-read', [AssignmentNotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('/notifications/unread', [AssignmentNotificationController::class, 'showUnread'])->name('notifications.unread');

    // View ni ko'rsatish va Word faylni yuklab olish routelari
});

// User routes
Route::middleware(['auth', 'role:User'])->group(function () {
    Route::get('/index', [ClientAllController::class, 'index'])->name('client.index');
    Route::get('/subtask', [ClientAllController::class, 'subtask'])->name('client.subtask');
    Route::get('/allsubtask', [ClientAllController::class, 'allsubtask'])->name('client.allsubtask');
    Route::get('/profile', [ClientAllController::class, 'editProfile'])->name('profile.edit');
    Route::post('/profile/update', [ClientAllController::class, 'updateProfile'])->name('profile.update');
    Route::resource('assignments', AssignmentController::class);
    Route::get('/assignments/file/{assignment}', [AssignmentController::class, 'viewFile'])->name('assignments.viewFile');
});

// Texnik xodimlar uchun davomat routes
Route::middleware(['auth', 'role:Texnik'])
    ->prefix('employee')
    ->name('employee.')
    ->group(function () {
        //Xodim kunlik kechikishlarni ko'rish routlari
        Route::get('/attendance', [AttendanceController::class, 'employeeIndex'])->name('attendance.index');
        //Xodim hisobotlarini ko'rish routlari
        Route::resource('reports', EmployeeReportController::class)->except(['destroy']);
        Route::delete('/reports/{report}', [EmployeeReportController::class, 'destroy'])
            ->name('reports.destroy')
            ->middleware('can:delete,report');
            Route::get('reports/{report}/download/{filename}', [EmployeeReportController::class, 'downloadAttachment'])
        ->name('reports.download-attachment');
    });
//Xodim hisobotlarini ko'rish routlari
Route::middleware(['auth', 'role:Texnik'])->group(function () {
    Route::redirect('/employee/xisobotlar', '/employee/reports');
});
// Umumiy routes
Route::middleware(['auth'])->group(function () {
    Route::resource('assignments', AssignmentController::class);
    Route::get('assignments/{assignment}/file', [AssignmentController::class, 'viewFile'])->name('assignments.viewFile');
});
