<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'date', 'morning_in', 'lunch_out', 'lunch_in', 'evening_out', 'morning_late', 'lunch_duration', 'early_leave', 'morning_comment', 'lunch_comment', 'evening_comment', 'day_comment'];

    protected $casts = [
        'date' => 'date',
        'morning_in' => 'datetime',
        'lunch_out' => 'datetime',
        'lunch_in' => 'datetime',
        'evening_out' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Automatik hisoblashlar
    public function calculateAll()
    {
        $this->calculateMorningLate();
        $this->calculateLunchDuration();
        $this->calculateEarlyLeave();
    }

    public function calculateMorningLate()
    {
        if (!$this->morning_in) {
            $this->morning_late = 0;
            return;
        }

        $workStart = Carbon::parse('09:00');
        $morningIn = Carbon::parse($this->morning_in);

        if ($morningIn->gt($workStart)) {
            $totalLateMinutes = $morningIn->diffInMinutes($workStart);

            // Agar abet vaqtidan keyin kelgan bo'lsa (13:00 dan keyin), 60 daqiqa ayirish
            if ($morningIn->hour >= 13) {
                $this->morning_late = max(0, $totalLateMinutes - 60);
            } else {
                $this->morning_late = $totalLateMinutes;
            }
        } else {
            $this->morning_late = 0;
        }
    }

    public function calculateLunchDuration()
    {
        if (!$this->lunch_out || !$this->lunch_in) {
            $this->lunch_duration = 0;
            return;
        }

        $lunchOut = Carbon::parse($this->lunch_out);
        $lunchIn = Carbon::parse($this->lunch_in);

        $duration = $lunchIn->diffInMinutes($lunchOut);
        $this->lunch_duration = max(0, $duration - 60); // 60 daqiqa dan ortiq qismi
    }

    public function calculateEarlyLeave()
    {
        if (!$this->evening_out) {
            $this->early_leave = 0;
            return;
        }

        $workEnd = Carbon::parse('18:00');
        $eveningOut = Carbon::parse($this->evening_out);

        if ($eveningOut->lt($workEnd)) {
            $this->early_leave = $workEnd->diffInMinutes($eveningOut);
        } else {
            $this->early_leave = 0;
        }
    }

    // Kechikish sababsizmi? - "Sababsiz" so'zi ham sababsiz hisoblanadi
    public function isMorningLateCauseless()
    {
        return $this->morning_late > 0 && (empty($this->morning_comment) || $this->morning_comment === 'Sababsiz');
    }

    // Abet uzoqmi va sababsizmi? - "Sababsiz" so'zi ham sababsiz hisoblanadi
    public function isLunchExtendedCauseless()
    {
        return $this->lunch_duration > 0 && (empty($this->lunch_comment) || $this->lunch_comment === 'Sababsiz');
    }

    // Erta ketish sababsizmi? - "Sababsiz" so'zi ham sababsiz hisoblanadi
    public function isEarlyLeaveCauseless()
    {
        return $this->early_leave > 0 && (empty($this->evening_comment) || $this->evening_comment === 'Sababsiz');
    }

    // Erta ketganmi?
    public function isEarlyLeave()
    {
        return $this->early_leave > 0;
    }

    // Umuman kelmaganmi?
    public function isAbsent()
    {
        return (!$this->morning_in && !$this->lunch_out && !$this->lunch_in && !$this->evening_out) || $this->morning_late > 479;
    }

    // Sana formati
    public function getFormattedDateAttribute()
    {
        return $this->date->format('d/m/Y');
    }

    // Haftalik statistika - BARCHA VAQT YO'QOTISHLARI
    public static function getWeeklyStats($userId)
    {
        $weekAgo = Carbon::now()->subDays(7);

        return self::where('user_id', $userId)
            ->where('date', '>=', $weekAgo)
            ->get()
            ->reduce(
                function ($stats, $attendance) {
                    // Ertalab kechikish
                    if ($attendance->morning_late > 0) {
                        if (empty($attendance->morning_comment) || $attendance->morning_comment === 'Sababsiz') {
                            $stats['sababsiz_ishda_bolmagan'] += $attendance->morning_late;
                        } else {
                            $stats['sababli_ishda_bolmagan'] += $attendance->morning_late;
                        }
                    }

                    // Abet kechikish
                    if ($attendance->lunch_duration > 0) {
                        if (empty($attendance->lunch_comment) || $attendance->lunch_comment === 'Sababsiz') {
                            $stats['sababsiz_ishda_bolmagan'] += $attendance->lunch_duration;
                        } else {
                            $stats['sababli_ishda_bolmagan'] += $attendance->lunch_duration;
                        }
                    }

                    // Erta ketish
                    if ($attendance->early_leave > 0) {
                        if (empty($attendance->evening_comment) || $attendance->evening_comment === 'Sababsiz') {
                            $stats['sababsiz_ishda_bolmagan'] += $attendance->early_leave;
                        } else {
                            $stats['sababli_ishda_bolmagan'] += $attendance->early_leave;
                        }
                    }

                    return $stats;
                },
                ['sababsiz_ishda_bolmagan' => 0, 'sababli_ishda_bolmagan' => 0],
            );
    }

    // Oylik statistika - BARCHA VAQT YO'QOTISHLARI
    public static function getMonthlyStats($userId)
    {
        $monthAgo = Carbon::now()->subDays(30);

        $data = self::where('user_id', $userId)->where('date', '>=', $monthAgo)->get();

        // DEBUG
        \Log::info('Monthly Stats Debug:', [
            'user_id' => $userId,
            'month_ago' => $monthAgo->format('Y-m-d'),
            'records_count' => $data->count(),
            'records' => $data->map(function ($a) {
                return [
                    'date' => $a->date->format('Y-m-d'),
                    'morning_late' => $a->morning_late,
                    'morning_comment' => $a->morning_comment,
                    'lunch_duration' => $a->lunch_duration,
                    'lunch_comment' => $a->lunch_comment,
                    'early_leave' => $a->early_leave,
                    'evening_comment' => $a->evening_comment,
                ];
            }),
        ]);

        return $data->reduce(
            function ($stats, $attendance) {
                // Ertalab kechikish
                if ($attendance->morning_late > 0) {
                    if (empty($attendance->morning_comment) || $attendance->morning_comment === 'Sababsiz') {
                        $stats['sababsiz_ishda_bolmagan'] += $attendance->morning_late;
                    } else {
                        $stats['sababli_ishda_bolmagan'] += $attendance->morning_late;
                    }
                }

                // Abet kechikish
                if ($attendance->lunch_duration > 0) {
                    if (empty($attendance->lunch_comment) || $attendance->lunch_comment === 'Sababsiz') {
                        $stats['sababsiz_ishda_bolmagan'] += $attendance->lunch_duration;
                    } else {
                        $stats['sababli_ishda_bolmagan'] += $attendance->lunch_duration;
                    }
                }

                // Erta ketish
                if ($attendance->early_leave > 0) {
                    if (empty($attendance->evening_comment) || $attendance->evening_comment === 'Sababsiz') {
                        $stats['sababsiz_ishda_bolmagan'] += $attendance->early_leave;
                    } else {
                        $stats['sababli_ishda_bolmagan'] += $attendance->early_leave;
                    }
                }

                return $stats;
            },
            ['sababsiz_ishda_bolmagan' => 0, 'sababli_ishda_bolmagan' => 0],
        );
    }

    // Formatli vaqt ko'rsatish uchun helper method
    public static function formatMinutes($minutes)
    {
        if ($minutes == 0) {
            return '0 daq';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return $hours . ' soat ' . ($remainingMinutes > 0 ? $remainingMinutes . ' daq' : '');
        }

        return $remainingMinutes . ' daq';
    }

    // Jami ishda bo'lmagan vaqt (barcha yo'qotishlar)
    public static function getTotalMissingTime($userId, $days = null)
    {
        $query = self::where('user_id', $userId);

        if ($days) {
            $query->where('date', '>=', Carbon::now()->subDays($days));
        }

        return $query->get()->sum(function ($attendance) {
            return $attendance->morning_late + $attendance->lunch_duration + $attendance->early_leave;
        });
    }

    // Sababsiz ishda bo'lmagan jami vaqt
    public static function getCauselessMissingTime($userId, $days = null)
    {
        $query = self::where('user_id', $userId);

        if ($days) {
            $query->where('date', '>=', Carbon::now()->subDays($days));
        }

        return $query->get()->sum(function ($attendance) {
            $total = 0;

            // Sababsiz ertalab kechikish
            if ($attendance->morning_late > 0 && (empty($attendance->morning_comment) || $attendance->morning_comment === 'Sababsiz')) {
                $total += $attendance->morning_late;
            }

            // Sababsiz abet kechikish
            if ($attendance->lunch_duration > 0 && (empty($attendance->lunch_comment) || $attendance->lunch_comment === 'Sababsiz')) {
                $total += $attendance->lunch_duration;
            }

            // Sababsiz erta ketish
            if ($attendance->early_leave > 0 && (empty($attendance->evening_comment) || $attendance->evening_comment === 'Sababsiz')) {
                $total += $attendance->early_leave;
            }

            return $total;
        });
    }

    // Sababli ishda bo'lmagan jami vaqt
    public static function getExcusedMissingTime($userId, $days = null)
    {
        $query = self::where('user_id', $userId);

        if ($days) {
            $query->where('date', '>=', Carbon::now()->subDays($days));
        }

        return $query->get()->sum(function ($attendance) {
            $total = 0;

            // Sababli ertalab kechikish (comment bor va "Sababsiz" emas)
            if ($attendance->morning_late > 0 && !empty($attendance->morning_comment) && $attendance->morning_comment !== 'Sababsiz') {
                $total += $attendance->morning_late;
            }

            // Sababli abet kechikish (comment bor va "Sababsiz" emas)
            if ($attendance->lunch_duration > 0 && !empty($attendance->lunch_comment) && $attendance->lunch_comment !== 'Sababsiz') {
                $total += $attendance->lunch_duration;
            }

            // Sababli erta ketish (comment bor va "Sababsiz" emas)
            if ($attendance->early_leave > 0 && !empty($attendance->evening_comment) && $attendance->evening_comment !== 'Sababsiz') {
                $total += $attendance->early_leave;
            }

            return $total;
        });
    }
}
