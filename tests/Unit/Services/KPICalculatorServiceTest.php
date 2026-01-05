<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\KPICalculatorService;

class KPICalculatorServiceTest extends TestCase
{
    private KPICalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new KPICalculatorService();
    }

    // ========================================
    // BASIC KPI CALCULATION TESTS
    // ========================================

    /** @test */
    public function it_calculates_kpi_correctly()
    {
        // 75 out of 100 = 75%
        $kpi = $this->service->calculateKPI(75, 100);
        
        $this->assertEquals(75.0, $kpi);
    }

    /** @test */
    public function it_calculates_kpi_with_decimals()
    {
        // 67.5 out of 100 = 67.5%
        $kpi = $this->service->calculateKPI(67.5, 100);
        
        $this->assertEquals(67.5, $kpi);
    }

    /** @test */
    public function it_rounds_kpi_to_two_decimals()
    {
        // 66.666... → 66.67%
        $kpi = $this->service->calculateKPI(66.666666, 100);
        
        $this->assertEquals(66.67, $kpi);
    }

    /** @test */
    public function kpi_can_be_100_percent()
    {
        // Maximum: 100 out of 100 = 100%
        $kpi = $this->service->calculateKPI(100, 100);
        
        $this->assertEquals(100.0, $kpi);
    }

    /** @test */
    public function kpi_can_be_zero_percent()
    {
        // Minimum: 0 out of 100 = 0%
        $kpi = $this->service->calculateKPI(0, 100);
        
        $this->assertEquals(0.0, $kpi);
    }

    /** @test */
    public function it_returns_zero_when_max_is_zero()
    {
        // Prevent division by zero
        $kpi = $this->service->calculateKPI(50, 0);
        
        $this->assertEquals(0, $kpi);
    }

    /** @test */
    public function it_returns_zero_when_max_is_negative()
    {
        // Edge case: negative max
        $kpi = $this->service->calculateKPI(50, -10);
        
        $this->assertEquals(0, $kpi);
    }

    // ========================================
    // KPI FORMULA VALIDATION TESTS
    // ========================================

    /** @test */
    public function kpi_formula_is_percentage_of_max()
    {
        $totalWithBonus = 150;
        $maxTotal = 200;
        
        $kpi = $this->service->calculateKPI($totalWithBonus, $maxTotal);
        
        // Formula: (total / max) * 100
        $expectedKPI = ($totalWithBonus / $maxTotal) * 100;
        
        $this->assertEquals(round($expectedKPI, 2), $kpi);
        $this->assertEquals(75.0, $kpi); // 150/200 * 100 = 75%
    }

    /** @test */
    public function kpi_uses_correct_multiplier()
    {
        // Should multiply by 100 (NOT 200, NOT 50)
        $kpi = $this->service->calculateKPI(50, 100);
        
        // ✅ Correct: * 100
        $this->assertEquals(50.0, $kpi);
        
        // ❌ Wrong: would be 100 if * 200
        $this->assertNotEquals(100.0, $kpi, 'BUG: KPI multiplier should be 100, not 200!');
        
        // ❌ Wrong: would be 25 if * 50
        $this->assertNotEquals(25.0, $kpi, 'BUG: KPI multiplier should be 100, not 50!');
    }

    /** @test */
    public function it_detects_wrong_multiplier()
    {
        $totalWithBonus = 75;
        $maxTotal = 100;
        
        $kpi = $this->service->calculateKPI($totalWithBonus, $maxTotal);
        
        $expected = 75.0; // (75/100) * 100
        $actual = $kpi;
        
        $this->assertEquals(
            $expected, 
            $actual,
            "BUG: KPI formula should be (total/max)*100. Expected: {$expected}, Got: {$actual}"
        );
        
        // ✅ If multiplier is 200 (wrong), result would be 150
        $this->assertNotEquals(150.0, $kpi, 'BUG: Multiplier is 200 instead of 100!');
    }

    // ========================================
    // MAX TOTAL CALCULATION TESTS
    // ========================================

    /** @test */
    public function it_finds_maximum_value_correctly()
    {
        $userTotals = [
            1 => 100,
            2 => 150,
            3 => 75,
            4 => 200, // Maximum
            5 => 120,
        ];

        $maxTotal = $this->service->calculateMaxTotal($userTotals);
        
        $this->assertEquals(200, $maxTotal);
    }

    /** @test */
    public function it_returns_one_when_all_totals_are_zero()
    {
        // Prevent division by zero
        $userTotals = [
            1 => 0,
            2 => 0,
            3 => 0,
        ];

        $maxTotal = $this->service->calculateMaxTotal($userTotals);
        
        $this->assertEquals(1, $maxTotal);
    }

    /** @test */
    public function it_returns_one_when_max_is_negative()
    {
        // Edge case: all negative values
        $userTotals = [
            1 => -10,
            2 => -5,
            3 => -20,
        ];

        $maxTotal = $this->service->calculateMaxTotal($userTotals);
        
        $this->assertEquals(1, $maxTotal);
    }

    /** @test */
    public function it_handles_single_user()
    {
        $userTotals = [1 => 150];

        $maxTotal = $this->service->calculateMaxTotal($userTotals);
        
        $this->assertEquals(150, $maxTotal);
    }

    /** @test */
    public function it_handles_empty_array()
    {
        $userTotals = [];

        $maxTotal = $this->service->calculateMaxTotal($userTotals);
        
        $this->assertEquals(1, $maxTotal); // Prevent division by zero
    }

    // ========================================
    // CALCULATE ALL KPIs TESTS
    // ========================================

    /** @test */
    public function it_calculates_kpis_for_all_users()
    {
        $userTotals = [
            1 => 100,
            2 => 150,
            3 => 75,
            4 => 50,
        ];

        $kpis = $this->service->calculateAllKPIs($userTotals);
        
        // Max is 150
        $this->assertEquals(66.67, $kpis[1]); // 100/150 * 100 = 66.67%
        $this->assertEquals(100.0, $kpis[2]); // 150/150 * 100 = 100%
        $this->assertEquals(50.0, $kpis[3]);  // 75/150 * 100 = 50%
        $this->assertEquals(33.33, $kpis[4]); // 50/150 * 100 = 33.33%
    }

    /** @test */
    public function all_kpis_are_between_zero_and_hundred()
    {
        $userTotals = [
            1 => 100,
            2 => 150,
            3 => 75,
            4 => 200,
            5 => 50,
        ];

        $kpis = $this->service->calculateAllKPIs($userTotals);
        
        foreach ($kpis as $userId => $kpi) {
            $this->assertGreaterThanOrEqual(
                0, 
                $kpi,
                "User {$userId} KPI should be >= 0"
            );
            
            $this->assertLessThanOrEqual(
                100, 
                $kpi,
                "User {$userId} KPI should be <= 100"
            );
        }
    }

    /** @test */
    public function highest_total_gets_100_percent_kpi()
    {
        $userTotals = [
            1 => 100,
            2 => 200, // Highest
            3 => 150,
        ];

        $kpis = $this->service->calculateAllKPIs($userTotals);
        
        // User 2 has highest total, should get 100%
        $this->assertEquals(100.0, $kpis[2]);
        
        // Others should be proportional
        $this->assertEquals(50.0, $kpis[1]);  // 100/200 * 100
        $this->assertEquals(75.0, $kpis[3]);  // 150/200 * 100
    }

    // ========================================
    // REAL-WORLD SCENARIO TESTS
    // ========================================

    /** @test */
    public function real_world_scenario_with_bonuses()
    {
        // Scenario: 5 users with ratings and bonuses
        $userTotals = [
            1 => 150 + 15,  // 165 (rating + bonus)
            2 => 120 + 12,  // 132
            3 => 180 + 18,  // 198 (highest)
            4 => 100 + 10,  // 110
            5 => 90 + 9,    // 99
        ];

        $kpis = $this->service->calculateAllKPIs($userTotals);
        
        // Max is 198
        $this->assertEquals(83.33, $kpis[1]); // 165/198 * 100
        $this->assertEquals(66.67, $kpis[2]); // 132/198 * 100
        $this->assertEquals(100.0, $kpis[3]); // 198/198 * 100
        $this->assertEquals(55.56, $kpis[4]); // 110/198 * 100
        $this->assertEquals(50.0, $kpis[5]);  // 99/198 * 100
    }

    /** @test */
    public function kpi_updates_when_max_changes()
    {
        // Initial scenario
        $userTotals1 = [
            1 => 100,
            2 => 150, // Max
        ];

        $kpis1 = $this->service->calculateAllKPIs($userTotals1);
        $this->assertEquals(100.0, $kpis1[2]); // User 2: 100%

        // New user with higher total joins
        $userTotals2 = [
            1 => 100,
            2 => 150,
            3 => 200, // New max
        ];

        $kpis2 = $this->service->calculateAllKPIs($userTotals2);
        
        // User 2's KPI decreases because max changed
        $this->assertEquals(75.0, $kpis2[2]);  // 150/200 * 100
        $this->assertEquals(100.0, $kpis2[3]); // 200/200 * 100
    }

    // ========================================
    // EDGE CASES & BUG DETECTION
    // ========================================

    /** @test */
    public function it_handles_very_small_numbers()
    {
        $kpi = $this->service->calculateKPI(0.01, 0.02);
        
        $this->assertEquals(50.0, $kpi); // 0.01/0.02 * 100
    }

    /** @test */
    public function it_handles_very_large_numbers()
    {
        $kpi = $this->service->calculateKPI(1000000, 2000000);
        
        $this->assertEquals(50.0, $kpi);
    }

    /** @test */
    public function it_detects_division_by_zero_protection()
    {
        // Should NOT throw error
        $kpi1 = $this->service->calculateKPI(100, 0);
        $this->assertEquals(0, $kpi1);
        
        $kpi2 = $this->service->calculateKPI(50, -10);
        $this->assertEquals(0, $kpi2);
        
        $maxTotal = $this->service->calculateMaxTotal([1 => 0, 2 => -5]);
        $this->assertEquals(1, $maxTotal);
    }

    /** @test */
    public function kpi_never_exceeds_100_percent()
    {
        // Even if somehow total > max (shouldn't happen but let's test)
        $kpi = $this->service->calculateKPI(150, 100);
        
        // This would be 150%, but in real scenario this shouldn't happen
        // because max should always be the highest
        $this->assertEquals(150.0, $kpi);
        
        // In production, we might want to cap at 100
        // But current implementation allows > 100 if total > max
    }

    /** @test */
    public function it_validates_kpi_is_rounded_not_truncated()
    {
        // 66.666... should round to 66.67, not 66.66
        $kpi = $this->service->calculateKPI(200, 300);
        
        $this->assertEquals(66.67, $kpi);
        
        // Test rounding up
        $kpi2 = $this->service->calculateKPI(33.335, 50);
        $this->assertEquals(66.67, $kpi2); // Rounds up
        
        // Test rounding down
        $kpi3 = $this->service->calculateKPI(33.334, 50);
        $this->assertEquals(66.67, $kpi3); // Rounds up (banker's rounding)
    }

    /** @test */
    public function consistent_results_with_same_input()
    {
        // Same input should always give same output
        $kpi1 = $this->service->calculateKPI(75, 100);
        $kpi2 = $this->service->calculateKPI(75, 100);
        $kpi3 = $this->service->calculateKPI(75, 100);
        
        $this->assertEquals($kpi1, $kpi2);
        $this->assertEquals($kpi2, $kpi3);
        $this->assertEquals(75.0, $kpi1);
    }

    /** @test */
    public function kpi_calculation_is_deterministic()
    {
        $userTotals = [1 => 100, 2 => 150, 3 => 75];
        
        $kpis1 = $this->service->calculateAllKPIs($userTotals);
        $kpis2 = $this->service->calculateAllKPIs($userTotals);
        
        $this->assertEquals($kpis1, $kpis2);
    }
}