<?php

namespace App\Http\Controllers\Employee;

use Carbon\Carbon;
use App\Models\Report;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    /**
     * Oylik xisobot sanalarini hisoblash (26-sanadan keyingi oyning 25-sanasigacha)
     */
    private function getCurrentMonthDates()
    {
        $today = Carbon::today();

        if ($today->day >= 26) {
            $startDate = $today->copy()->day(26);
            $endDate = $today->copy()->addMonth()->day(25);
        } else {
            $startDate = $today->copy()->subMonth()->day(26);
            $endDate = $today->copy()->day(25);
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    /**
     * Istalgan hafta uchun sana hisoblash
     */
    private function getWeekDates($weekOffset = 0)
    {
        $today = Carbon::today();
        $targetDate = $today->copy()->addWeeks($weekOffset);

        $monday = $targetDate->copy()->startOfWeek(Carbon::MONDAY);
        $friday = $monday->copy()->addDays(4);

        return [
            'start_date' => $monday,
            'end_date' => $friday,
        ];
    }

    /**
     * Oxirgi N hafta ro'yxatini olish
     */
    private function getAvailableWeeks($weeksCount = 6)
    {
        $weeks = [];
        $userId = auth()->id();

        for ($i = 0; $i >= -$weeksCount; $i--) {
            $dates = $this->getWeekDates($i);
            $hasReport = Report::forUser($userId)->weekly()->where('start_date', $dates['start_date'])->exists();

            $weeks[] = [
                'offset' => $i,
                'start_date' => $dates['start_date'],
                'end_date' => $dates['end_date'],
                'has_report' => $hasReport,
                'is_current' => $i === 0,
                'period_text' => $dates['start_date']->format('d.m.Y') . ' - ' . $dates['end_date']->format('d.m.Y'),
                'week_description' => $this->getWeekDescription($i),
            ];
        }

        return collect($weeks);
    }

    /**
     * Hafta tavsifini olish
     */
    private function getWeekDescription($offset)
    {
        if ($offset === 0) {
            return 'Joriy hafta';
        } elseif ($offset === -1) {
            return 'O\'tgan hafta';
        } else {
            $weeksAgo = abs($offset);
            return "{$weeksAgo} hafta oldin";
        }
    }

    /**
     * Oxirgi N oy ro'yxatini olish
     */
    private function getAvailableMonths($monthsCount = 3)
    {
        $months = [];
        $userId = auth()->id();

        for ($i = 0; $i >= -$monthsCount; $i--) {
            $dates = $this->getMonthDates($i);
            $hasReport = Report::forUser($userId)->monthly()->where('start_date', $dates['start_date'])->exists();

            $monthNames = [
                1 => 'Yanvar',
                2 => 'Fevral',
                3 => 'Mart',
                4 => 'Aprel',
                5 => 'May',
                6 => 'Iyun',
                7 => 'Iyul',
                8 => 'Avgust',
                9 => 'Sentabr',
                10 => 'Oktabr',
                11 => 'Noyabr',
                12 => 'Dekabr',
            ];

            $monthNumber = $dates['end_date']->month;
            $year = $dates['end_date']->year;
            $monthName = $monthNames[$monthNumber];

            $months[] = [
                'offset' => $i,
                'start_date' => $dates['start_date'],
                'end_date' => $dates['end_date'],
                'has_report' => $hasReport,
                'is_current' => $i === 0,
                'period_text' => $dates['start_date']->format('d.m.Y') . ' - ' . $dates['end_date']->format('d.m.Y'),
                'month_name' => $monthName . ' ' . $year,
                'month_description' => $this->getMonthDescription($i),
            ];
        }

        return collect($months);
    }

    /**
     * Istalgan oy uchun sana hisoblash
     */
    private function getMonthDates($monthOffset = 0)
    {
        $today = Carbon::today();
        $targetDate = $today->copy()->addMonths($monthOffset);

        if ($monthOffset === 0) {
            return $this->getCurrentMonthDates();
        }

        if ($targetDate->day >= 26) {
            $startDate = $targetDate->copy()->day(26);
            $endDate = $targetDate->copy()->addMonth()->day(25);
        } else {
            $startDate = $targetDate->copy()->subMonth()->day(26);
            $endDate = $targetDate->copy()->day(25);
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    /**
     * Oy tavsifini olish
     */
    private function getMonthDescription($offset)
    {
        if ($offset === 0) {
            return 'Joriy oy';
        } elseif ($offset === -1) {
            return 'O\'tgan oy';
        } else {
            $monthsAgo = abs($offset);
            return "{$monthsAgo} oy oldin";
        }
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user->hasRole('Texnik')) {
            abort(403, 'Bu sahifaga kirishga ruxsatingiz yo\'q');
        }

        $type = $request->get('type', 'all');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Report::forUser($user->id)
            ->orderByRaw(
                "
                CASE
                    WHEN approved_count = 0 AND rejected_count = 0 THEN 1
                    WHEN rejected_count > 0 THEN 2
                    ELSE 3
                END ASC
            ",
            )
            ->orderByRaw('YEAR(start_date) DESC')
            ->orderByRaw('MONTH(start_date) DESC')
            ->orderByRaw(
                "
                CASE
                    WHEN type = 'monthly' THEN 0
                    ELSE 1
                END ASC
            ",
            )
            ->orderBy('start_date', 'desc');

        if ($type === 'weekly') {
            $query->weekly();
        } elseif ($type === 'monthly') {
            $query->monthly();
        }

        if ($dateFrom && $dateTo) {
            $query->inDateRange($dateFrom, $dateTo);
        }

        $reports = $query->paginate(10);

        $currentWeek = $this->getWeekDates(0);
        $currentMonth = $this->getCurrentMonthDates();

        $hasWeeklyReport = Report::forUser($user->id)->weekly()->where('start_date', $currentWeek['start_date'])->exists();
        $hasMonthlyReport = Report::forUser($user->id)->monthly()->where('start_date', $currentMonth['start_date'])->exists();

        $availableWeeks = $this->getAvailableWeeks(6);
        $availableMonths = $this->getAvailableMonths(3);

        return view('employee.reports.index', compact('reports', 'type', 'dateFrom', 'dateTo', 'hasWeeklyReport', 'hasMonthlyReport', 'currentWeek', 'currentMonth', 'availableWeeks', 'availableMonths'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();

        if (!$user->hasRole('Texnik')) {
            abort(403, 'Bu sahifaga kirishga ruxsatingiz yo\'q');
        }

        $type = $request->get('type', 'weekly');

        if ($type === 'weekly') {
            $weekOffset = (int) $request->get('week_offset', 0);
            $dates = $this->getWeekDates($weekOffset);
            $title = 'Haftalik xisobot (' . $this->getWeekDescription($weekOffset) . ')';
            $period = $dates['start_date']->format('d.m.Y') . ' - ' . $dates['end_date']->format('d.m.Y');

            $existingReport = Report::forUser($user->id)->where('type', 'weekly')->where('start_date', $dates['start_date'])->first();

            if ($existingReport) {
                return redirect()->route('employee.reports.edit', $existingReport)->with('info', 'Bu hafta uchun allaqachon xisobot mavjud. Uni tahrirlashingiz mumkin.');
            }
        } else {
            $monthOffset = (int) $request->get('month_offset', 0);
            $dates = $this->getMonthDates($monthOffset);
            $title = 'Oylik xisobot (' . $this->getMonthDescription($monthOffset) . ')';

            $monthNames = [
                1 => 'Yanvar',
                2 => 'Fevral',
                3 => 'Mart',
                4 => 'Aprel',
                5 => 'May',
                6 => 'Iyun',
                7 => 'Iyul',
                8 => 'Avgust',
                9 => 'Sentabr',
                10 => 'Oktabr',
                11 => 'Noyabr',
                12 => 'Dekabr',
            ];

            $monthNumber = $dates['end_date']->month;
            $year = $dates['end_date']->year;
            $period = $monthNames[$monthNumber] . ' ' . $year;

            $existingReport = Report::forUser($user->id)->where('type', 'monthly')->where('start_date', $dates['start_date'])->first();

            if ($existingReport) {
                return redirect()->route('employee.reports.edit', $existingReport)->with('info', 'Bu oy uchun allaqachon xisobot mavjud. Uni tahrirlashingiz mumkin.');
            }
        }

        return view('employee.reports.create', compact('type', 'title', 'period', 'dates'));
    }

    public function store(StoreReportRequest $request)
    {
        $user = auth()->user();

        if (!$user->hasRole('Texnik')) {
            abort(403, 'Bu sahifaga kirishga ruxsatingiz yo\'q');
        }

        $existingReport = Report::forUser($user->id)->where('type', $request->type)->where('start_date', $request->start_date)->first();

        if ($existingReport) {
            return back()->withErrors(['error' => 'Bu davr uchun allaqachon xisobot mavjud']);
        }

        // ✅ DEBUG: Fayllarni tekshirish
        \Log::info('Files check:', [
            'has_files' => $request->hasFile('attachments'),
            'files' => $request->file('attachments'),
            'all_request' => $request->all(),
        ]);

        // Fayllarni yuklash
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // ✅ Faylni tekshirish
                \Log::info('Processing file:', [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ]);

                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('reports/attachments', $filename, 'public');

                $attachments[] = [
                    'original_name' => $file->getClientOriginalName(),
                    'filename' => $filename,
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_at' => now()->toDateTimeString(),
                ];

                \Log::info('File uploaded:', [
                    'path' => $path,
                    'filename' => $filename,
                ]);
            }
        }

        \Log::info('Attachments array:', ['attachments' => $attachments]);

        $report = Report::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'content' => $request->content,
            'attachments' => !empty($attachments) ? json_encode($attachments) : null,
        ]);

        \Log::info('Report created:', [
            'id' => $report->id,
            'attachments' => $report->attachments,
        ]);

        return redirect()->route('employee.reports.index')->with('success', '✅ Xisobot muvaffaqiyatli saqlandi');
    }

    public function show(Report $report)
    {
        $user = auth()->user();

        if (!$user->hasRole('Texnik') || $report->user_id !== $user->id) {
            abort(403, 'Bu sahifaga kirishga ruxsatingiz yo\'q');
        }

        $reviews = $report->getFormattedReviews();

        // ✅ Fayllarni olish
        $attachments = json_decode($report->attachments, true) ?? [];

        return view('employee.reports.show', compact('report', 'reviews', 'attachments'));
    }

    public function edit(Report $report)
    {
        $user = auth()->user();

        if (!$user->hasRole('Texnik') || $report->user_id !== $user->id) {
            abort(403, 'Bu sahifaga kirishga ruxsatingiz yo\'q');
        }

        if (!$report->isEditable()) {
            return redirect()->route('employee.reports.show', $report)->with('error', 'Bu xisobot endi tahrirlab bo\'lmaydi');
        }

        $title = $report->type === 'weekly' ? 'Haftalik xisobot tahrirlash' : 'Oylik xisobot tahrirlash';
        $period = $report->start_date->format('d.m.Y') . ' - ' . $report->end_date->format('d.m.Y');

        // ✅ Fayllarni olish
        $attachments = json_decode($report->attachments, true) ?? [];

        return view('employee.reports.edit', compact('report', 'title', 'period', 'attachments'));
    }

    public function update(UpdateReportRequest $request, Report $report)
    {
        $user = auth()->user();

        if (!$user->hasRole('Texnik') || $report->user_id !== $user->id) {
            abort(403, 'Bu sahifaga kirishga ruxsatingiz yo\'q');
        }

        if (!$report->isEditable()) {
            return redirect()->route('employee.reports.show', $report)->with('error', '❌ Bu xisobot endi tahrirlab bo\'lmaydi');
        }

        // Mavjud fayllar
        $existingAttachments = json_decode($report->attachments, true) ?? [];

        // Fayllarni o'chirish
        if ($request->has('remove_attachments')) {
            foreach ($request->remove_attachments as $filenameToRemove) {
                foreach ($existingAttachments as $key => $attachment) {
                    if ($attachment['filename'] === $filenameToRemove) {
                        Storage::disk('public')->delete($attachment['path']);
                        unset($existingAttachments[$key]);
                        break;
                    }
                }
            }
            $existingAttachments = array_values($existingAttachments);
        }

        // Yangi fayllarni yuklash
        if ($request->hasFile('attachments')) {
            if (count($existingAttachments) + count($request->file('attachments')) > 5) {
                return back()->withErrors(['attachments' => '❌ Jami 5 ta fayldan ortiq bo\'lishi mumkin emas']);
            }

            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('reports/attachments', $filename, 'public');

                $existingAttachments[] = [
                    'original_name' => $file->getClientOriginalName(),
                    'filename' => $filename,
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }
        }

        $report->update([
            'content' => $request->content,
            'attachments' => !empty($existingAttachments) ? json_encode($existingAttachments) : null,
        ]);

        return redirect()->route('employee.reports.show', $report)->with('success', '✅ Xisobot muvaffaqiyatli yangilandi');
    }

    public function destroy(Report $report)
    {
        $user = auth()->user();

        if (!$user->hasRole('Texnik') || $report->user_id !== $user->id) {
            abort(403, 'Bu sahifaga kirishga ruxsatingiz yo\'q');
        }

        if (!$report->isEditable()) {
            return redirect()->route('employee.reports.index')->with('error', 'Bu xisobot o\'chirib bo\'lmaydi');
        }

        // Fayllarni o'chirish
        $attachments = json_decode($report->attachments, true) ?? [];
        foreach ($attachments as $attachment) {
            Storage::disk('public')->delete($attachment['path']);
        }

        $report->delete();

        return redirect()->route('employee.reports.index')->with('success', 'Xisobot o\'chirildi');
    }

    /**
     * Faylni yuklab olish
     */
    public function downloadAttachment(Report $report, $filename)
    {
        $user = auth()->user();

        if (!$user->hasRole('Texnik') || $report->user_id !== $user->id) {
            abort(403, 'Bu sahifaga kirishga ruxsatingiz yo\'q');
        }

        $attachments = json_decode($report->attachments, true) ?? [];

        foreach ($attachments as $attachment) {
            if ($attachment['filename'] === $filename) {
                $filePath = storage_path('app/public/' . $attachment['path']);

                if (file_exists($filePath)) {
                    return response()->download($filePath, $attachment['original_name']);
                }
            }
        }

        abort(404, 'Fayl topilmadi');
    }

    public function getAvailableWeeksAjax(Request $request)
    {
        $user = auth()->user();

        if (!$user->hasRole('Texnik')) {
            return response()->json(['error' => 'Ruxsat yo\'q'], 403);
        }

        $weeks = $this->getAvailableWeeks(6);

        return response()->json([
            'weeks' => $weeks,
        ]);
    }

    public function getAvailableMonthsAjax(Request $request)
    {
        $user = auth()->user();

        if (!$user->hasRole('Texnik')) {
            return response()->json(['error' => 'Ruxsat yo\'q'], 403);
        }

        $months = $this->getAvailableMonths(3);

        return response()->json([
            'months' => $months,
        ]);
    }
}
