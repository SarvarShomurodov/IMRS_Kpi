<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // Admin paneli - barcha texnik xodimlar ro'yxati
    public function adminIndex()
    {
        if (!auth()->user()->hasRole('Admin') && !auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Bu sahifaga kirishga ruxsatingiz yo\'q');
        }

        $users = User::role('Texnik')->get();
        $today = Carbon::today()->format('Y-m-d');

        return view('admin.attendance.index', compact('users', 'today'));
    }

    // Admin - bitta xodimning davomat formasi
    public function adminShow(User $user, Request $request)
    {
        if (!auth()->user()->hasRole('Admin') && !auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Bu sahifaga kirishga ruxsatingiz yo\'q');
        }

        // Faqat Texnik role'li xodimlar uchun
        if (!$user->hasRole('Texnik')) {
            abort(404, 'Foydalanuvchi topilmadi');
        }

        $date = $request->get('date', Carbon::today()->format('Y-m-d'));

        $attendance = Attendance::firstOrNew([
            'user_id' => $user->id,
            'date' => $date,
        ]);

        return view('admin.attendance.form', compact('user', 'attendance', 'date'));
    }

    // Admin - davomat ma'lumotlarini saqlash
    public function adminStore(Request $request, User $user)
    {
        if (!auth()->user()->hasRole('Admin') && !auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Bu sahifaga kirishga ruxsatingiz yo\'q');
        }

        // Config dan o'qish
        $allowedEmails = config('admin.attendance_edit_emails', []);

        // Faqat ruxsat berilgan emaillar o'zgartira oladi
        if (!in_array(auth()->user()->email, $allowedEmails)) {
            abort(403, 'Sizda davomat ma\'lumotlarini o\'zgartirish huquqi yo\'q');
        }

        // Faqat Texnik role'li xodimlar uchun
        if (!$user->hasRole('Texnik')) {
            abort(404, 'Foydalanuvchi topilmadi');
        }

        $request->validate([
            'date' => 'required|date',
            'morning_in' => 'nullable|date_format:H:i',
            'lunch_out' => 'nullable|date_format:H:i',
            'lunch_in' => 'nullable|date_format:H:i',
            'evening_out' => 'nullable|date_format:H:i',
            'morning_comment_type' => 'nullable|in:sababsiz,sababli',
            'morning_comment_text' => 'nullable|string',
            'lunch_comment_type' => 'nullable|in:sababsiz,sababli',
            'lunch_comment_text' => 'nullable|string',
            'evening_comment_type' => 'nullable|in:sababsiz,sababli',
            'evening_comment_text' => 'nullable|string',
            'day_comment' => 'nullable|string',
        ]);

        // Izohlarni to'g'ri format qilish
        $morningComment = $this->processComment($request->morning_comment_type, $request->morning_comment_text);
        $lunchComment = $this->processComment($request->lunch_comment_type, $request->lunch_comment_text);
        $eveningComment = $this->processComment($request->evening_comment_type, $request->evening_comment_text);

        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $request->date,
            ],
            [
                'morning_in' => $request->morning_in,
                'lunch_out' => $request->lunch_out,
                'lunch_in' => $request->lunch_in,
                'evening_out' => $request->evening_out,
                'morning_comment' => $morningComment,
                'lunch_comment' => $lunchComment,
                'evening_comment' => $eveningComment,
                'day_comment' => $request->day_comment,
            ],
        );

        // Avtomatik hisoblashlar
        $attendance->calculateAll();
        $attendance->save();

        return redirect()
            ->route('admin.attendance.show', ['user' => $user->id, 'date' => $request->date])
            ->with('success', 'Davomat ma\'lumotlari saqlandi');
    }

    // Izoh tipini qayta ishlash
    private function processComment($commentType, $commentText)
    {
        if ($commentType === 'sababsiz') {
            return 'Sababsiz';
        } elseif ($commentType === 'sababli' && !empty($commentText)) {
            return trim($commentText);
        }

        return null;
    }

    // Xodim paneli - o'z davomat ko'rish
    public function employeeIndex(Request $request)
    {
        $user = auth()->user();

        if (!$user->hasRole('Texnik')) {
            abort(403, 'Bu sahifaga kirishga ruxsatingiz yo\'q');
        }

        $perPage = 10;
        $attendances = Attendance::where('user_id', $user->id)->orderBy('date', 'desc')->paginate($perPage);

        // Statistikalar - daqiqalarda
        $weeklyStats = Attendance::getWeeklyStats($user->id);
        $monthlyStats = Attendance::getMonthlyStats($user->id);

        // Qo'shimcha statistikalar (ixtiyoriy)
        $additionalStats = [
            'weekly_total_missing' => Attendance::getTotalMissingTime($user->id, 7),
            'monthly_total_missing' => Attendance::getTotalMissingTime($user->id, 30),
            'weekly_causeless' => Attendance::getCauselessMissingTime($user->id, 7),
            'monthly_causeless' => Attendance::getCauselessMissingTime($user->id, 30),
            'weekly_excused' => Attendance::getExcusedMissingTime($user->id, 7),
            'monthly_excused' => Attendance::getExcusedMissingTime($user->id, 30),
        ];

        return view('employee.attendance.index', compact('attendances', 'weeklyStats', 'monthlyStats', 'additionalStats'));
    }

    // API endpointlar (AJAX uchun)
    public function getAttendanceData(User $user, $date)
    {
        if (!auth()->user()->hasRole('Admin') && !auth()->user()->hasRole('Super Admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $attendance = Attendance::where('user_id', $user->id)->where('date', $date)->first();

        return response()->json($attendance);
    }
}
