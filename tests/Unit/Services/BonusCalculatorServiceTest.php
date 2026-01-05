<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Services\BonusCalculatorService;
// use Illuminate\Foundation\Testing\RefreshDatabase;

class BonusCalculatorServiceTest extends TestCase
{
    // use RefreshDatabase;

    private BonusCalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BonusCalculatorService();
    }

    // ========================================
    // BASIC TESTS
    // ========================================

    /** @test */
    public function it_calculates_global_average_correctly()
    {
        $eligibleUsers = collect([
            (object)['id' => 1],
            (object)['id' => 2],
            (object)['id' => 3],
        ]);

        $newUsers = collect([
            (object)['id' => 4],
        ]);

        $userRatings = [
            1 => 100,
            2 => 200,
            3 => 150,
            4 => 50,
        ];

        $globalAvg = $this->service->calculateGlobalAverage(
            $eligibleUsers,
            $newUsers,
            $userRatings
        );

        $this->assertEquals(125, $globalAvg); // (100+200+150+50)/4 = 125
    }

    /** @test */
    public function it_returns_zero_when_no_users()
    {
        $globalAvg = $this->service->calculateGlobalAverage(
            collect([]),
            collect([]),
            []
        );

        $this->assertEquals(0, $globalAvg);
    }

    // ========================================
    // PROJECT BONUS CALCULATION TESTS
    // ========================================

    /** @test */
    public function it_calculates_project_bonuses_with_correct_formulas()
    {
        $project1 = 1;
        
        $baseUsers = collect([
            (object)['id' => 1, 'project_id' => $project1],
            (object)['id' => 2, 'project_id' => $project1],
        ]);
        
        $eligibleUsers = $baseUsers;
        $userRatings = [1 => 120, 2 => 100]; // avg = 110
        $globalAvg = 80;
        $dominantProjects = [1 => $project1, 2 => $project1];
        
        $projectBonuses = $this->service->calculateProjectBonuses(
            $baseUsers,
            $eligibleUsers,
            collect([]),
            $userRatings,
            $globalAvg,
            $dominantProjects
        );
        
        $projectAvg = 110;
        
        // ✅ FORMULA CHECKS
        $this->assertEquals(
            round($projectAvg * 0.10, 2), 
            $projectBonuses[$project1]['bonus_10'],
            'bonus_10 should be: projectAvg * 0.10'
        );
        
        $this->assertEquals(
            round($projectAvg * 0.05, 2), 
            $projectBonuses[$project1]['bonus_5'],
            'bonus_5 should be: projectAvg * 0.05'
        );
        
        // ✅ CONCRETE VALUES
        $this->assertEquals(11, $projectBonuses[$project1]['bonus_10']); // 110 * 0.10
        $this->assertEquals(5.5, $projectBonuses[$project1]['bonus_5']); // 110 * 0.05
    }

    /** @test */
    public function it_gives_bonus_only_when_project_avg_above_global()
    {
        $project1 = 1;
        $project2 = 2;

        $baseUsers = collect([
            (object)['id' => 1, 'project_id' => $project1],
            (object)['id' => 2, 'project_id' => $project2],
        ]);

        $eligibleUsers = $baseUsers;
        $userRatings = [
            1 => 150, // Project 1 avg = 150
            2 => 50,  // Project 2 avg = 50
        ];
        $globalAvg = 100;
        $dominantProjects = [1 => $project1, 2 => $project2];

        $projectBonuses = $this->service->calculateProjectBonuses(
            $baseUsers,
            $eligibleUsers,
            collect([]),
            $userRatings,
            $globalAvg,
            $dominantProjects
        );

        // Project 1: 150 > 100 → has bonus
        $this->assertTrue($projectBonuses[$project1]['has_bonus']);
        $this->assertEquals(15, $projectBonuses[$project1]['bonus_10']); // 150 * 0.10
        $this->assertEquals(7.5, $projectBonuses[$project1]['bonus_5']); // 150 * 0.05

        // Project 2: 50 < 100 → NO bonus
        $this->assertFalse($projectBonuses[$project2]['has_bonus']);
        $this->assertEquals(0, $projectBonuses[$project2]['bonus_10']);
        $this->assertEquals(0, $projectBonuses[$project2]['bonus_5']);
    }

    /** @test */
    public function it_handles_multiple_projects_correctly()
    {
        $project1 = 1;
        $project2 = 2;
        $project3 = 3;

        $baseUsers = collect([
            (object)['id' => 1, 'project_id' => $project1],
            (object)['id' => 2, 'project_id' => $project1],
            (object)['id' => 3, 'project_id' => $project2],
            (object)['id' => 4, 'project_id' => $project2],
            (object)['id' => 5, 'project_id' => $project3],
            (object)['id' => 6, 'project_id' => $project3],
        ]);

        $eligibleUsers = $baseUsers;
        $userRatings = [
            1 => 100, 2 => 120, // Project 1: avg = 110
            3 => 80, 4 => 70,   // Project 2: avg = 75
            5 => 150, 6 => 160, // Project 3: avg = 155
        ];
        $globalAvg = 100;
        $dominantProjects = [
            1 => $project1, 2 => $project1,
            3 => $project2, 4 => $project2,
            5 => $project3, 6 => $project3,
        ];

        $projectBonuses = $this->service->calculateProjectBonuses(
            $baseUsers,
            $eligibleUsers,
            collect([]),
            $userRatings,
            $globalAvg,
            $dominantProjects
        );

        // ✅ ALL 3 PROJECTS RETURNED
        $this->assertCount(3, $projectBonuses);

        // ✅ PROJECT 1: 110 > 100 → bonus
        $this->assertEquals(110, $projectBonuses[$project1]['project_avg']);
        $this->assertTrue($projectBonuses[$project1]['has_bonus']);
        $this->assertEquals(11, $projectBonuses[$project1]['bonus_10']);
        $this->assertEquals(5.5, $projectBonuses[$project1]['bonus_5']);

        // ✅ PROJECT 2: 75 < 100 → no bonus
        $this->assertEquals(75, $projectBonuses[$project2]['project_avg']);
        $this->assertFalse($projectBonuses[$project2]['has_bonus']);
        $this->assertEquals(0, $projectBonuses[$project2]['bonus_10']);
        $this->assertEquals(0, $projectBonuses[$project2]['bonus_5']);

        // ✅ PROJECT 3: 155 > 100 → bonus
        $this->assertEquals(155, $projectBonuses[$project3]['project_avg']);
        $this->assertTrue($projectBonuses[$project3]['has_bonus']);
        $this->assertEquals(15.5, $projectBonuses[$project3]['bonus_10']);
        $this->assertEquals(7.75, $projectBonuses[$project3]['bonus_5']);
    }

    // ========================================
    // USER BONUS CALCULATION TESTS
    // ========================================

    /** @test */
    public function user_above_project_avg_gets_10_percent_bonus()
    {
        $userId = 1;
        $userRating = 150; // Above project avg (100)
        $eligibleUsers = collect([(object)['id' => 1]]);
        
        $projectBonuses = [
            1 => [
                'has_bonus' => true,
                'project_avg' => 100,
                'bonus_10' => 10, // 10% of 100
                'bonus_5' => 5,   // 5% of 100
            ]
        ];
        
        $dominantProjects = [1 => 1];

        $bonus = $this->service->calculateUserBonus(
            $userId,
            $userRating,
            $eligibleUsers,
            collect([]),
            $projectBonuses,
            $dominantProjects
        );

        $this->assertEquals(10, $bonus); // Gets bonus_10
    }

    /** @test */
    public function user_below_project_avg_gets_5_percent_bonus()
    {
        $userId = 1;
        $userRating = 80; // Below project avg (100)
        $eligibleUsers = collect([(object)['id' => 1]]);
        
        $projectBonuses = [
            1 => [
                'has_bonus' => true,
                'project_avg' => 100,
                'bonus_10' => 10,
                'bonus_5' => 5, // Should get this
            ]
        ];
        
        $dominantProjects = [1 => 1];

        $bonus = $this->service->calculateUserBonus(
            $userId,
            $userRating,
            $eligibleUsers,
            collect([]),
            $projectBonuses,
            $dominantProjects
        );

        $this->assertEquals(5, $bonus); // Gets bonus_5
    }

    /** @test */
    public function user_gets_no_bonus_when_project_has_no_bonus()
    {
        $bonus = $this->service->calculateUserBonus(
            1,
            100,
            collect([(object)['id' => 1]]),
            collect([]),
            [1 => ['has_bonus' => false]], // Project has no bonus
            [1 => 1]
        );

        $this->assertEquals(0, $bonus);
    }

    /** @test */
    public function new_user_without_rating_gets_no_bonus()
    {
        $userId = 1;
        $eligibleUsers = collect([]); // Not eligible
        $newUsers = collect([]);      // Not in new users
        
        $bonus = $this->service->calculateUserBonus(
            $userId,
            0,
            $eligibleUsers,
            $newUsers,
            [1 => ['has_bonus' => true, 'bonus_10' => 10]],
            [1 => 1]
        );

        $this->assertEquals(0, $bonus);
    }

    /** @test */
    public function eligible_user_with_rating_gets_bonus()
    {
        $userId = 1;
        $eligibleUsers = collect([(object)['id' => 1]]); // Eligible
        
        $bonus = $this->service->calculateUserBonus(
            $userId,
            120,
            $eligibleUsers,
            collect([]),
            [1 => ['has_bonus' => true, 'project_avg' => 100, 'bonus_10' => 10, 'bonus_5' => 5]],
            [1 => 1]
        );

        $this->assertEquals(10, $bonus); // 120 > 100, gets 10%
    }

    /** @test */
    public function new_user_with_rating_gets_bonus()
    {
        $userId = 1;
        $eligibleUsers = collect([]);                    // Not old eligible
        $newUsers = collect([(object)['id' => 1]]);      // But new with rating
        
        $bonus = $this->service->calculateUserBonus(
            $userId,
            90,
            $eligibleUsers,
            $newUsers,
            [1 => ['has_bonus' => true, 'project_avg' => 100, 'bonus_10' => 10, 'bonus_5' => 5]],
            [1 => 1]
        );

        $this->assertEquals(5, $bonus); // 90 < 100, gets 5%
    }

    // ========================================
    // EDGE CASES & BUG DETECTION
    // ========================================

    /** @test */
    public function it_detects_foreach_return_bug()
    {
        // This test catches the bug where return happens inside foreach
        $project1 = 1;
        $project2 = 2;

        $baseUsers = collect([
            (object)['id' => 1, 'project_id' => $project1],
            (object)['id' => 2, 'project_id' => $project2],
        ]);

        $projectBonuses = $this->service->calculateProjectBonuses(
            $baseUsers,
            $baseUsers,
            collect([]),
            [1 => 100, 2 => 120],
            80,
            [1 => $project1, 2 => $project2]
        );

        $this->assertGreaterThanOrEqual(
            2, 
            count($projectBonuses), 
            'BUG: Only first project returned. Check foreach - should not return inside loop!'
        );
    }

    /** @test */
    public function it_validates_bonus_percentages_are_correct()
    {
        $project1 = 1;
        $baseUsers = collect([(object)['id' => 1, 'project_id' => $project1]]);
        
        $projectBonuses = $this->service->calculateProjectBonuses(
            $baseUsers,
            $baseUsers,
            collect([]),
            [1 => 200],
            100,
            [1 => $project1]
        );

        $projectAvg = 200;
        
        // ✅ CHECK: bonus_10 = 10% (NOT 20%, NOT 15%)
        $this->assertEquals(
            20, 
            $projectBonuses[$project1]['bonus_10'],
            'BUG: bonus_10 should be 10% (0.10) of project avg'
        );
        
        // ✅ CHECK: bonus_5 = 5% (NOT 9%, NOT 7%)
        $this->assertEquals(
            10, 
            $projectBonuses[$project1]['bonus_5'],
            'BUG: bonus_5 should be 5% (0.05) of project avg'
        );
    }

    /** @test */
    public function it_detects_wrong_bonus_5_percentage()
    {
        $project1 = 1;
        $baseUsers = collect([(object)['id' => 1, 'project_id' => $project1]]);
        
        $projectBonuses = $this->service->calculateProjectBonuses(
            $baseUsers,
            $baseUsers,
            collect([]),
            [1 => 100],
            50,
            [1 => $project1]
        );

        $projectAvg = 100;
        $expected = round($projectAvg * 0.05, 2); // 5.0
        $actual = $projectBonuses[$project1]['bonus_5'];
        
        $this->assertEquals(
            $expected, 
            $actual,
            "BUG: bonus_5 should be 5% (0.05). Expected: {$expected}, Got: {$actual}"
        );
        
        // ✅ EXPLICITLY CHECK it's NOT 9%
        $this->assertNotEquals(
            round($projectAvg * 0.09, 2), 
            $actual,
            'BUG: bonus_5 is 9% (0.09) but should be 5% (0.05)!'
        );
    }

    /** @test */
    public function it_detects_wrong_bonus_10_percentage()
    {
        $project1 = 1;
        $baseUsers = collect([(object)['id' => 1, 'project_id' => $project1]]);
        
        $projectBonuses = $this->service->calculateProjectBonuses(
            $baseUsers,
            $baseUsers,
            collect([]),
            [1 => 100],
            50,
            [1 => $project1]
        );

        $projectAvg = 100;
        $expected = round($projectAvg * 0.10, 2); // 10.0
        $actual = $projectBonuses[$project1]['bonus_10'];
        
        $this->assertEquals(
            $expected, 
            $actual,
            "BUG: bonus_10 should be 10% (0.10). Expected: {$expected}, Got: {$actual}"
        );
        
        // ✅ EXPLICITLY CHECK it's NOT 20%
        $this->assertNotEquals(
            round($projectAvg * 0.20, 2), 
            $actual,
            'BUG: bonus_10 is 20% (0.20) but should be 10% (0.10)!'
        );
    }

    /** @test */
    public function bonus_is_zero_when_project_avg_equals_global()
    {
        $project1 = 1;
        $baseUsers = collect([(object)['id' => 1, 'project_id' => $project1]]);
        
        $projectBonuses = $this->service->calculateProjectBonuses(
            $baseUsers,
            $baseUsers,
            collect([]),
            [1 => 100],
            100, // Same as project avg
            [1 => $project1]
        );

        // projectAvg (100) NOT > globalAvg (100) → no bonus
        $this->assertFalse($projectBonuses[$project1]['has_bonus']);
        $this->assertEquals(0, $projectBonuses[$project1]['bonus_10']);
        $this->assertEquals(0, $projectBonuses[$project1]['bonus_5']);
    }

    /** @test */
    public function bonus_is_zero_when_project_avg_is_negative()
    {
        // Edge case: negative ratings shouldn't happen but let's test
        $project1 = 1;
        $baseUsers = collect([(object)['id' => 1, 'project_id' => $project1]]);
        
        $projectBonuses = $this->service->calculateProjectBonuses(
            $baseUsers,
            $baseUsers,
            collect([]),
            [1 => -50], // Negative rating
            100,
            [1 => $project1]
        );

        // projectAvg (-50) NOT > 0 → no bonus
        $this->assertFalse($projectBonuses[$project1]['has_bonus']);
        $this->assertEquals(0, $projectBonuses[$project1]['bonus_10']);
        $this->assertEquals(0, $projectBonuses[$project1]['bonus_5']);
    }
}