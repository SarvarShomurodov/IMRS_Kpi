<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TaskAssignment;

class FillTaskAssignmentProjects extends Command
{
    protected $signature = 'taskassignments:fill-projects';
    protected $description = 'Fill project_id for existing task assignments based on user project';

    public function handle()
    {
        $assignments = TaskAssignment::whereNull('project_id')->with('user')->get();
        
        foreach ($assignments as $assignment) {
            if ($assignment->user) {
                $assignment->project_id = $assignment->user->project_id;
                $assignment->save();
            }
        }
        
        $this->info('Project IDs filled successfully!');
    }
}