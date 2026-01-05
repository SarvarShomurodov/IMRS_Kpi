<?php

namespace App\Services;

use Carbon\Carbon;

class DateRangeService
{
    /**
     * 26-25 oralig'ini hisoblash (Default)
     * ✅ LOGIKA O'ZGARMAYDI - Sizning eski kodingiz
     */
    public static function getDefaultRange(?string $from = null, ?string $to = null): array
    {
        if ($from && $to) {
            return [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay()
            ];
        }

        $today = Carbon::now();
        
        if ($today->day < 26) {
            // O'tgan oyning 26-si dan hozirgi oyning 25-si
            return [
                Carbon::create($today->year, $today->month, 1)->subMonth()->day(26)->startOfDay(),
                Carbon::create($today->year, $today->month, 25)->endOfDay()
            ];
        }
        
        // Hozirgi oyning 26-si dan keyingi oyning 25-si
        return [
            Carbon::create($today->year, $today->month, 26)->startOfDay(),
            Carbon::create($today->year, $today->month, 1)->addMonth()->day(25)->endOfDay()
        ];
    }

    /**
     * Yil va oy bo'yicha range
     * ✅ LOGIKA O'ZGARMAYDI
     */
    public static function getRangeByYearMonth(int $year, int $month): array
    {
        return [
            Carbon::createFromDate($year, $month, 1)->subMonth()->day(26)->startOfDay(),
            Carbon::createFromDate($year, $month, 1)->day(25)->endOfDay()
        ];
    }

    /**
     * Format to string
     */
    public static function formatDates(array $dates): array
    {
        return [
            'from' => $dates[0]->toDateString(),
            'to' => $dates[1]->toDateString()
        ];
    }
}