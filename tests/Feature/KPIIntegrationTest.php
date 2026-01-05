<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\SubTask;
use App\Models\Assignment;
use App\Models\TaskAssignment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KPIIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function kpi_calculation_remains_consistent_across_scenarios()
    {
        // ✅ SCENARIO 1: Basic case
        $this->setupBasicScenario();
        $response1 = $this->get(route('task.swod'));
        $response1->assertStatus(200);
        
        $assignments1 = $response1->viewData('assignments');
        $maxTotal1 = $response1->viewData('maxTotalWithBonus');

        // ✅ SCENARIO 2: With new user
        $this->addNewUser();
        $response2 = $this->get(route('task.swod'));
        
        // Assert: Old users' KPI should not change drastically
        $assignments2 = $response2->viewData('assignments');
        
        // Check that existing user KPIs are similar
        $this->assertKPIStability($assignments1, $assignments2);
    }

    /** @test */
    public function bonus_calculation_is_correct_for_project_above_global_avg()
    {
        // Setup
        $project1 = Project::factory()->create(['name' => 'High Performing']);
        $project2 = Project::factory()->create(['name' => 'Low Performing']);
        
        $user1 = User::factory()->create(['project_id' => $project1->id]);
        $user2 = User::factory()->create(['project_id' => $project2->id]);
        
        $task = Task::factory()->create();
        $subtask = SubTask::factory()->create(['task_id' => $task->id, 'min' => 0, 'max' => 100]);
        
        $from = Carbon::now()->subMonth()->day(26);
        
        // User 1 - High rating (150)
        TaskAssignment::create([
            'user_id' => $user1->id,
            'subtask_id' => $subtask->id,
            'project_id' => $project1->id,
            'rating' => 150,
            'addDate' => $from->copy()->addDays(5),
        ]);
        
        // User 2 - Low rating (50)
        TaskAssignment::create([
            'user_id' => $user2->id,
            'subtask_id' => $subtask->id,
            'project_id' => $project2->id,
            'rating' => 50,
            'addDate' => $from->copy()->addDays(5),
        ]);

        // Act
        $response = $this->get(route('task.swod'));
        $assignments = $response->viewData('assignments');

        // Assert
        $user1Data = $assignments[$user1->id];
        $user2Data = $assignments[$user2->id];
        
        // User 1's project (150) > global avg (100), so should have bonus
        $this->assertGreaterThan(0, $user1Data['bonus']);
        
        // User 2's project (50) < global avg (100), so NO bonus
        $this->assertEquals(0, $user2Data['bonus']);
    }

    private function setupBasicScenario()
    {
        $project = Project::factory()->create();
        $user = User::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create();
        $subtask = SubTask::factory()->create(['task_id' => $task->id]);
        
        TaskAssignment::create([
            'user_id' => $user->id,
            'subtask_id' => $subtask->id,
            'project_id' => $project->id,
            'rating' => 100,
            'addDate' => Carbon::now()->subDays(5),
        ]);
    }

    private function addNewUser()
    {
        $project = Project::first();
        $user = User::factory()->create(['project_id' => $project->id]);
        $subtask = SubTask::first();
        
        TaskAssignment::create([
            'user_id' => $user->id,
            'subtask_id' => $subtask->id,
            'project_id' => $project->id,
            'rating' => 80,
            'addDate' => Carbon::now()->subDays(3),
        ]);
    }

    private function assertKPIStability($assignments1, $assignments2)
    {
        foreach ($assignments1 as $userId => $data1) {
            if (isset($assignments2[$userId])) {
                $data2 = $assignments2[$userId];
                
                // KPI should not change by more than 5%
                $diff = abs($data1['kpi'] - $data2['kpi']);
                $this->assertLessThan(5, $diff, "KPI changed too much for user {$userId}");
            }
        }
    }
}