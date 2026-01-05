<?php

namespace App\Repositories;

use App\Models\Assignment;
use App\Models\TaskAssignment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AssignmentRepository
{
    /**
     * ✅ ESKI ASSIGNMENTLAR - LOGIKA O'ZGARMAYDI
     * Faqat chunk qo'shildi (million+ ma'lumotlar uchun)
     */
    public function getOldAssignments(array $userIds, $from, $to, bool $useChunk = true): Collection
    {
        $query = TaskAssignment::with([
                'subtask:id,title,min,max,task_id',
                'subtask.task:id,taskName',
                'project:id,name',
                'user:id,firstName,lastName,project_id'
            ])
            ->whereIn('user_id', $userIds)
            ->whereBetween('addDate', [$from, $to])
            ->select(['id', 'user_id', 'subtask_id', 'project_id', 'rating', 'comment', 'addDate']);

        if (!$useChunk) {
            return $query->get();
        }

        // ✅ CHUNK - Million+ ma'lumotlar uchun
        $assignments = collect();
        $query->chunk(1000, function ($chunk) use (&$assignments) {
            $assignments = $assignments->merge($chunk);
        });

        return $assignments;
    }

    /**
     * ✅ YANGI ASSIGNMENTLAR - LOGIKA O'ZGARMAYDI
     */
    public function getNewAssignments(array $userIds, $from, $to, bool $useChunk = true): Collection
    {
        $query = Assignment::with([
                'task:id,taskName',
                'subtask:id,title,min,max,task_id',
                'user:id,firstName,lastName,project_id,deleted_at'
            ])
            ->whereIn('user_id', $userIds)
            ->whereNotNull('task_id')
            ->whereNotNull('subtask_id')
            ->whereNotNull('rating')
            ->whereBetween('date', [$from, $to])
            ->select(['id', 'user_id', 'task_id', 'subtask_id', 'rating', 'comment', 'date']);

        if (!$useChunk) {
            return $query->get();
        }

        $assignments = collect();
        $query->chunk(1000, function ($chunk) use (&$assignments) {
            $assignments = $assignments->merge($chunk);
        });

        return $assignments;
    }

    /**
     * ✅ RATING AGGREGATSIYA - DATABASE LEVEL
     * Bu eng katta optimizatsiya - memory tejash
     */
    public function getUserRatingsOptimized(array $userIds, $from, $to): array
    {
        // Eski assignmentlar - database da sum
        $oldRatings = DB::table('task_assignments')
            ->whereIn('user_id', $userIds)
            ->whereBetween('addDate', [$from, $to])
            ->select('user_id', DB::raw('COALESCE(SUM(rating), 0) as total'))
            ->groupBy('user_id')
            ->pluck('total', 'user_id')
            ->toArray();

        // Yangi assignmentlar
        $newRatings = DB::table('assignments')
            ->whereIn('user_id', $userIds)
            ->whereNotNull('task_id')
            ->whereNotNull('subtask_id')
            ->whereNotNull('rating')
            ->whereBetween('date', [$from, $to])
            ->select('user_id', DB::raw('COALESCE(SUM(rating), 0) as total'))
            ->groupBy('user_id')
            ->pluck('total', 'user_id')
            ->toArray();

        // ✅ BIRLASHTIRISH - AYNAN SIZNING LOGIKANGIZ
        $combined = [];
        foreach ($userIds as $userId) {
            $combined[$userId] = ($oldRatings[$userId] ?? 0) + ($newRatings[$userId] ?? 0);
        }

        return $combined;
    }

    /**
     * ✅ NORMALIZATSIYA - LOGIKA O'ZGARMAYDI
     */
    public function normalizeAssignment($assignment): object
    {
        $user = $assignment->user ?? null;
        
        return (object) [
            'id' => $assignment->id ?? null,
            'user_id' => $assignment->user_id,
            'rating' => (float) $assignment->rating,
            'project_id' => $user ? $user->project_id : null,
            'addDate' => $assignment->date ?? $assignment->addDate,
            'comment' => $assignment->comment ?? null,
            'subtask' => (object) [
                'id' => $assignment->subtask_id ?? ($assignment->subtask->id ?? null),
                'title' => $assignment->subtask->title ?? 'N/A',
                'min' => $assignment->subtask->min ?? 0,
                'max' => $assignment->subtask->max ?? 0,
                'task' => (object) [
                    'id' => $assignment->task_id ?? ($assignment->subtask->task->id ?? null),
                    'taskName' => ($assignment->task->taskName ?? null) 
                        ?? ($assignment->subtask->task->taskName ?? 'N/A'),
                ],
            ],
            'project' => (object) [
                'name' => ($user && $user->project) ? $user->project->name : 'N/A',
            ],
        ];
    }

    /**
     * ✅ BIRLASHTIRILGAN ASSIGNMENTLAR
     */
    public function getMergedAssignments(array $userIds, $from, $to): Collection
    {
        $old = $this->getOldAssignments($userIds, $from, $to);
        $new = $this->getNewAssignments($userIds, $from, $to);
        
        $normalizedNew = $new->map(fn($a) => $this->normalizeAssignment($a));
        
        return $old->concat($normalizedNew);
    }

    /**
     * Bitta user uchun assignmentlar
     */
    public function getUserAssignments(int $userId, $from, $to): Collection
    {
        return $this->getMergedAssignments([$userId], $from, $to);
    }
}