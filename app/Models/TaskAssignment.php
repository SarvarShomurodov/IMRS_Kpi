<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'subtask_id', 
        'user_id', 
        'project_id',  // QO'SHILDI
        'rating', 
        'comment', 
        'addDate',
        'project_id_updated_at',
    ];

    protected $casts = [
        'rating' => 'float',
    ];
    
    public function subtask()
    {
        return $this->belongsTo(SubTask::class, 'subtask_id')->with('task');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    // YANGI RELATION
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    
    public function getTaskAttribute()
    {
        return $this->subtask ? $this->subtask->task : null;
    }
}