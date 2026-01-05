<?php

namespace App\Services;

class KPICalculatorService
{
    /**
     * ✅ MAKSIMUM TOTAL
     * Edge case: bo'sh array uchun 1 qaytaradi
     */
    public function calculateMaxTotal(array $userTotalsWithBonus): float
    {
        // ✅ Bo'sh array check
        if (empty($userTotalsWithBonus)) {
            return 1; // Prevent division by zero
        }

        $max = max(array_values($userTotalsWithBonus));
        return $max > 0 ? $max : 1; // 0 ga bo'linmaslik uchun
    }

    /**
     * ✅ KPI HISOBLASH
     * Formula: (totalWithBonus / maxTotal) * 100
     */
    public function calculateKPI(float $totalWithBonus, float $maxTotalWithBonus): float
    {
        if ($maxTotalWithBonus <= 0) {
            return 0;
        }

        return round(($totalWithBonus / $maxTotalWithBonus) * 100, 2);
    }

    /**
     * ✅ Barcha userlar uchun KPI
     */
    public function calculateAllKPIs(array $userTotalsWithBonus): array
    {
        $maxTotal = $this->calculateMaxTotal($userTotalsWithBonus);

        $kpis = [];
        foreach ($userTotalsWithBonus as $userId => $total) {
            $kpis[$userId] = $this->calculateKPI($total, $maxTotal);
        }

        return $kpis;
    }
}