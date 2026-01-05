<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Report extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'type', 'start_date', 'end_date', 'content','attachments', 'is_editable', 'admin_reviews', 'approved_count', 'rejected_count'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'admin_reviews' => 'array',
        'is_editable' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Joriy haftalik xisobot oraliqlarini olish
    public static function getCurrentWeekDates()
    {
        $startDate = Carbon::now()->startOfWeek(); // Dushanba
        $endDate = Carbon::now()->endOfWeek()->subDays(2); // Juma

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    // Joriy oylik xisobot oraliqlarini olish (26-sanadan keyingi oyning 25-sanasigacha)
    public static function getCurrentMonthDates()
    {
        $today = Carbon::today();

        // Hozirgi sanani tekshirish
        if ($today->day >= 26) {
            // 26-sanadan keyin - joriy oyning 26 sidan keyingi oyning 25 sigacha
            $startDate = $today->copy()->day(26);
            $endDate = $today->copy()->addMonth()->day(25);
        } else {
            // 26-sanadan oldin - o'tgan oyning 26 sidan joriy oyning 25 sigacha
            $startDate = $today->copy()->subMonth()->day(26);
            $endDate = $today->copy()->day(25);
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    // Admin ko'rib chiqishi mumkinmi?
    public function canBeReviewedByAdmin($adminId)
    {
        $reviews = $this->admin_reviews ?? [];

        // Agar allaqachon ko'rib chiqilgan bo'lsa va bu admin emas
        if (count($reviews) > 0 && !isset($reviews[$adminId])) {
            return false;
        }

        // Agar bu admin allaqachon ko'rib chiqgan bo'lsa (faqat yangilash uchun)
        if (isset($reviews[$adminId])) {
            return true;
        }

        // Agar hech kim ko'rib chiqmagan bo'lsa
        return count($reviews) == 0;
    }

    // Admin ko'rib chiqish qo'shish
    public function addAdminReview($adminId, $status, $comment = null)
    {
        if (!$this->canBeReviewedByAdmin($adminId)) {
            return false;
        }

        $reviews = $this->admin_reviews ?? [];
        $reviews[$adminId] = [
            'status' => $status, // 'approved' yoki 'rejected'
            'comment' => $comment,
            'reviewed_at' => now()->toDateTimeString(),
        ];

        $this->admin_reviews = $reviews;

        // Statistikalarni yangilash
        $this->approved_count = collect($reviews)->where('status', 'approved')->count();
        $this->rejected_count = collect($reviews)->where('status', 'rejected')->count();

        // 1 ta admin tasdiqlagan bo'lsa, tahrirlash imkonini yopish
        if ($this->approved_count >= 1) {
            $this->is_editable = false;
        }

        $this->save();
        return true;
    }

    // Barcha adminlar tasdiqlagan bo'lsa
    public function isFullyApproved()
    {
        return $this->approved_count >= 1;
    }

    // Xisobot tahrirlash mumkinmi?
    public function isEditable()
    {
        return $this->is_editable && !$this->isFullyApproved();
    }

    // Xisobot oraliq matnini olish - OY NOMI BILAN
    public function getPeriodTextAttribute()
    {
        if ($this->type === 'weekly') {
            // Haftalik uchun to'g'ri hafta oralig'ini hisoblash
            $startDate = $this->start_date->copy();

            // Agar start_date allaqachon dushanba bo'lmasa, o'sha haftaning dushanbasi
            $monday = $startDate->startOfWeek(Carbon::MONDAY);
            $friday = $monday->copy()->addDays(4); // Juma kuni

            return $monday->format('d.m.Y') . ' - ' . $friday->format('d.m.Y');
        } else {
            // Oylik xisobot uchun tugash oyining nomini ko'rsatish
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

            $monthNumber = $this->end_date->month;
            $year = $this->end_date->year;

            return $monthNames[$monthNumber] . ' ' . $year;
        }
    }

    // Admin ko'rib chiqishlarini formatlangan holda olish
    public function getFormattedReviews()
    {
        $reviews = $this->admin_reviews ?? [];
        $formatted = [];

        foreach ($reviews as $adminId => $review) {
            // Admin ID ni integer ga o'tkazish
            $adminId = intval($adminId);

            \Log::info("Admin ID: {$adminId}");
            \Log::info('Review data: ' . json_encode($review));

            $admin = User::find($adminId);

            // Admin topildi-topilmaganini tekshirish
            \Log::info('Admin found: ' . ($admin ? $admin->full_name : 'NOT FOUND'));

            // Status tekshirish
            $status = $review['status'] ?? 'unknown';
            $isApproved = $status === 'approved';

            $formatted[] = [
                'admin_name' => $admin ? $admin->full_name : 'Unknown Admin',
                'status' => $status,
                'comment' => $review['comment'] ?? null,
                'reviewed_at' => isset($review['reviewed_at']) ? Carbon::parse($review['reviewed_at'])->format('d.m.Y H:i') : 'N/A',
                'is_approved' => $isApproved, // Aniq true/false qiymat
            ];
        }

        \Log::info('Formatted reviews: ' . json_encode($formatted));
        return $formatted;
    }

    // Haftalik xisobot uchun scope
    public function scopeWeekly($query)
    {
        return $query->where('type', 'weekly');
    }

    // Oylik xisobot uchun scope
    public function scopeMonthly($query)
    {
        return $query->where('type', 'monthly');
    }

    // Foydalanuvchi bo'yicha scope
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Tasdiqlanmagan xisobotlar uchun scope
    public function scopePendingReview($query)
    {
        return $query->where('approved_count', '<', 1)->where('rejected_count', '<', 1);
    }

    // Vaqt oralig'i bo'yicha filter
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
    }
}
