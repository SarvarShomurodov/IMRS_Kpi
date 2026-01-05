<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user->hasRole('Admin')) {
            abort(403, 'Bu sahifaga kirishga ruxsatingiz yo\'q');
        }

        $type = $request->get('type', 'all');
        $status = $request->get('status', 'all');
        $userId = $request->get('user_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Report::with('user')
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
            ->orderByRaw('MONTH(start_date) DESC')->orderByRaw("
                CASE
                    WHEN type = 'monthly' THEN 1
                    ELSE 2
                END ASC
            ")->orderByRaw("
                CASE
                    WHEN type = 'weekly' THEN start_date
                    ELSE '1900-01-01'
                END DESC
            ");

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($type === 'weekly') {
            $query->where('type', 'weekly');
        } elseif ($type === 'monthly') {
            $query->where('type', 'monthly');
        }

        if ($status === 'pending') {
            $query->where('approved_count', 0)->where('rejected_count', 0);
        } elseif ($status === 'approved') {
            $query->where('approved_count', '>=', 1);
        } elseif ($status === 'rejected') {
            $query->where('rejected_count', '>', 0);
        }

        if ($dateFrom && $dateTo) {
            $query->where('start_date', '>=', $dateFrom)->where('end_date', '<=', $dateTo);
        }

        $reports = $query->paginate(15);

        $employees = User::role('Texnik')->get();

        $baseQuery = Report::query();

        if ($userId) {
            $baseQuery->where('user_id', $userId);
        }

        if ($dateFrom && $dateTo) {
            $baseQuery->whereBetween('start_date', [$dateFrom, $dateTo]);
        }

        $stats = [
            'total_reports' => (clone $baseQuery)->count(),
            'pending_reports' => (clone $baseQuery)->where('approved_count', 0)->where('rejected_count', 0)->count(),
            'approved_reports' => (clone $baseQuery)->where('approved_count', '>=', 1)->count(),
            'rejected_reports' => (clone $baseQuery)->where('rejected_count', '>', 0)->count(),
            'weekly_reports' => (clone $baseQuery)->where('type', 'weekly')->count(),
            'monthly_reports' => (clone $baseQuery)->where('type', 'monthly')->count(),
        ];

        $selectedUser = $userId ? User::find($userId) : null;
        $currentFilters = $request->only(['type', 'status', 'date_from', 'date_to']);

        if ($userId) {
            $currentFilters['user_id'] = $userId;
        }

        return view('admin.reports.index', compact('reports', 'employees', 'stats', 'type', 'status', 'userId', 'dateFrom', 'dateTo', 'selectedUser', 'currentFilters'));
    }

    public function show(Report $report)
    {
        $user = auth()->user();

        if (!$user->hasRole('Admin')) {
            abort(403, 'Bu sahifaga kirishga ruxsatingiz yo\'q');
        }

        $reviews = $report->getFormattedReviews();
        $canReview = $report->canBeReviewedByAdmin($user->id);
        $myReview = null;

        if (isset($report->admin_reviews[$user->id])) {
            $myReview = $report->admin_reviews[$user->id];
        }

        // Fayllarni olish
        $attachments = json_decode($report->attachments, true) ?? [];

        return view('admin.reports.show', compact('report', 'reviews', 'canReview', 'myReview', 'attachments'));
    }

    public function review(Request $request, Report $report)
    {
        $user = auth()->user();

        if (!$user->hasRole('Admin')) {
            abort(403, 'Bu sahifaga kirishga ruxsatingiz yo\'q');
        }

        $reviews = $report->admin_reviews ?? [];
        if (count($reviews) > 0 && !isset($reviews[$user->id])) {
            return back()->with('error', 'Bu xisobotni boshqa administrator allaqachon ko\'rib chiqgan');
        }

        if (!$report->canBeReviewedByAdmin($user->id)) {
            return back()->with('error', 'Bu xisobotni ko\'rib chiqa olmaysiz');
        }

        $request->validate(
            [
                'status' => 'required|in:approved,rejected',
                'comment' => 'nullable|string|max:500',
            ],
            [
                'status.required' => 'Holat tanlanishi shart',
                'comment.max' => 'Izoh 500 ta belgidan oshmasligi kerak',
            ],
        );

        $report->addAdminReview($user->id, $request->status, $request->comment);

        $statusText = $request->status === 'approved' ? 'tasdiqlandi' : 'rad etildi';

        $userId = $request->get('user_id');
        $routeParams = ['report' => $report];
        if ($userId) {
            $routeParams['user_id'] = $userId;
        }

        return redirect()
            ->route('admin.reports.show', $routeParams)
            ->with('success', "Xisobot {$statusText}");
    }

    public function updateReview(Request $request, Report $report)
    {
        $user = auth()->user();

        if (!$user->hasRole('Admin')) {
            abort(403, 'Bu sahifaga kirishga ruxsatingiz yo\'q');
        }

        $reviews = $report->admin_reviews ?? [];
        if (!isset($reviews[$user->id])) {
            return back()->with('error', 'Siz bu xisobotni ko\'rib chiqmagansiz');
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'comment' => 'nullable|string|max:500',
        ]);

        $reviews[$user->id] = [
            'status' => $request->status,
            'comment' => $request->comment,
            'reviewed_at' => now()->toDateTimeString(),
        ];

        $report->admin_reviews = $reviews;
        $report->approved_count = collect($reviews)->where('status', 'approved')->count();
        $report->rejected_count = collect($reviews)->where('status', 'rejected')->count();

        if ($report->approved_count >= 1) {
            $report->is_editable = false;
        } else {
            $report->is_editable = true;
        }

        $report->save();

        $statusText = $request->status === 'approved' ? 'tasdiqlandi' : 'rad etildi';

        $userId = $request->get('user_id');
        $routeParams = ['report' => $report];
        if ($userId) {
            $routeParams['user_id'] = $userId;
        }

        return redirect()
            ->route('admin.reports.show', $routeParams)
            ->with('success', "Ko'rib chiqish yangilandi: {$statusText}");
    }

    public function analytics(Request $request)
    {
        $user = auth()->user();

        if (!$user->hasRole('Admin')) {
            abort(403, 'Bu sahifaga kirishga ruxsatingiz yo\'q');
        }

        $month = $request->get('month', Carbon::now()->format('Y-m'));
        $selectedDate = Carbon::parse($month . '-01');

        $startDate = $selectedDate->copy()->subMonth()->day(26);
        $endDate = $selectedDate->copy()->day(25);

        $analytics = [
            'total_employees' => User::role('Texnik')->count(),
            'reports_submitted' => Report::whereBetween('start_date', [$startDate, $endDate])->count(),
            'weekly_reports' => Report::weekly()
                ->whereBetween('start_date', [$startDate, $endDate])
                ->count(),
            'monthly_reports' => Report::monthly()
                ->whereBetween('start_date', [$startDate, $endDate])
                ->count(),
            'approved_reports' => Report::where('approved_count', '>=', 1)
                ->whereBetween('start_date', [$startDate, $endDate])
                ->count(),
            'pending_reports' => Report::pendingReview()
                ->whereBetween('start_date', [$startDate, $endDate])
                ->count(),
        ];

        $employeeStats = User::role('Texnik')
            ->withCount([
                'reports as total_reports' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate]);
                },
                'reports as approved_reports' => function ($query) use ($startDate, $endDate) {
                    $query->where('approved_count', '>=', 1)->whereBetween('start_date', [$startDate, $endDate]);
                },
                'reports as pending_reports' => function ($query) use ($startDate, $endDate) {
                    $query->where('approved_count', '<', 1)->whereBetween('start_date', [$startDate, $endDate]);
                },
            ])
            ->get();

        $dailyStats = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dailyStats[] = [
                'date' => $date->format('d.m'),
                'reports' => Report::whereDate('start_date', $date)->count(),
            ];
        }

        return view('admin.reports.analytics', compact('analytics', 'employeeStats', 'dailyStats', 'month', 'startDate', 'endDate'));
    }

    public function exportSingle(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user->hasRole('Admin')) {
            abort(403, 'Bu sahifaga kirishga ruxsatingiz yo\'q');
        }

        $userId = $request->get('user_id');
        $query = Report::with('user')->where('id', $id);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $report = $query->firstOrFail();

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->getDocInfo()->setCreator('IMRS System');
        $phpWord->getDocInfo()->setTitle($report->user->full_name . ' - Xisobot');

        // Sahifa o'lchamlarini sozlash
        $section = $phpWord->addSection([
            'marginLeft' => 1134,
            'marginRight' => 1134,
            'marginTop' => 1134,
            'marginBottom' => 1134,
        ]);

        // Sarlavha
        $titleStyle = ['name' => 'Times New Roman', 'size' => 15, 'bold' => true];
        $titleParagraph = ['alignment' => 'center', 'spaceAfter' => 100];

        $section->addText($report->user->project->name, $titleStyle, $titleParagraph);
        $section->addText('tomonidan ' . $report->period_text . 'da bajarilgan ishlar to\'g\'risida', $titleStyle, $titleParagraph);
        $section->addText('MA\'LUMOT.', $titleStyle, $titleParagraph);

        $section->addTextBreak(1);

        // Jadval yaratish
        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 100,
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
        ];

        $phpWord->addTableStyle('ReportTable', $tableStyle);
        $table = $section->addTable('ReportTable');

        // Jadval sarlavhasi
        $table->addRow(400);
        $headerCellStyle = ['valign' => 'center', 'bgColor' => 'FFFFFF'];
        $headerTextStyle = ['name' => 'Times New Roman', 'size' => 14, 'bold' => true];
        $centerAlign = ['alignment' => 'center'];

        $table->addCell(2000, $headerCellStyle)->addText('Ijrochilar', $headerTextStyle, $centerAlign);
        $table->addCell(1800, $headerCellStyle)->addText('Lavozimi', $headerTextStyle, $centerAlign);
        $table->addCell(6200, $headerCellStyle)->addText('Bajarilgan ishlar', $headerTextStyle, $centerAlign);

        // Ma'lumotlar qatori
        $table->addRow();
        $cellStyle = ['valign' => 'top', 'bgColor' => 'FFFFFF'];
        $textStyle = ['name' => 'Times New Roman', 'size' => 14];
        $italicStyle = ['name' => 'Times New Roman', 'size' => 14, 'italic' => true];

        // Ijrochilar ustuni
        $cell1 = $table->addCell(2000, $cellStyle);
        $cell1->addText($report->user->full_name, $textStyle, $centerAlign);

        // Lavozimi ustuni
        $cell2 = $table->addCell(1800, $cellStyle);
        $position = $report->user->position ?? 'Topilmadi';
        // "mutaxasis" so'zi bo'lsa italikda qilish
        if (stripos($position, 'mutaxasis') !== false) {
            $cell2->addText($position, $italicStyle, $centerAlign);
        } else {
            $cell2->addText($position, $textStyle, $centerAlign);
        }

        // Bajarilgan ishlar ustuni
        $contentCell = $table->addCell(6200, $cellStyle);
        $this->addFormattedContentToCell($contentCell, $report->content);

        $fileName = 'Xisobot_' . $report->user->full_name . '_' . now()->format('Y_m_d') . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    private function addFormattedContentToCell($cell, $htmlContent)
    {
        $textStyle = ['name' => 'Times New Roman', 'size' => 14];

        // Both (justified) alignment - ikkala tomonga tekislash
        $paragraphStyle = [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH,
            'spaceAfter' => 0,
            'spaceBefore' => 0,
            'lineHeight' => 1.0,
        ];

        // <li> taglarni ajratib olish
        preg_match_all('/<li>(.*?)<\/li>/is', $htmlContent, $matches);

        if (!empty($matches[1])) {
            // Har bir li elementi uchun
            foreach ($matches[1] as $index => $item) {
                // HTML teglarni tozalash
                $item = strip_tags($item);
                $item = html_entity_decode($item, ENT_QUOTES, 'UTF-8');
                $item = trim($item);

                // &nbsp; va boshqa maxsus belgilarni tozalash
                $item = str_replace(['&nbsp;', '\r\n', '\r', '\n'], ' ', $item);
                $item = preg_replace('/\s+/', ' ', $item); // Ko'p bo'shliqlarni bitta qilish
                $item = trim($item);

                if (!empty($item)) {
                    // Raqam bilan matnni birlashtirish
                    $numberText = $index + 1 . '. ' . $item;
                    $cell->addText($numberText, $textStyle, $paragraphStyle);
                }
            }
        } else {
            // Agar <li> tag bo'lmasa, oddiy matn sifatida qo'shish
            $content = strip_tags($htmlContent);
            $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
            $content = str_replace(['&nbsp;', '\r\n', '\r', '\n'], ' ', $content);
            $content = preg_replace('/\s+/', ' ', $content);
            $content = trim($content);

            if (!empty($content)) {
                $cell->addText($content, $textStyle, $paragraphStyle);
            }
        }
    }

    private function addFormattedContent($section, $htmlContent, $textStyle)
    {
        try {
            \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlContent, false, false);
        } catch (Exception $e) {
            $content = strip_tags($htmlContent);
            $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');

            $paragraphs = preg_split('/\n+/', $content);
            foreach ($paragraphs as $paragraph) {
                $paragraph = trim($paragraph);
                if (!empty($paragraph)) {
                    $section->addText($paragraph, $textStyle);
                    $section->addTextBreak(0.3);
                }
            }
        }
    }

    /**
     * Faylni yuklab olish (Admin uchun)
     */
    public function downloadAttachment(Report $report, $filename)
    {
        $user = auth()->user();

        if (!$user->hasRole('Admin')) {
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
}
