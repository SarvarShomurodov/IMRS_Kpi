<?php

namespace App\Models;

use App\Models\User;
use App\Models\Task;
use App\Models\SubTask;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\AssignmentUpdatedNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assignment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'task_id',           // ✅ YANGI
        'subtask_id',        // ✅ YANGI
        'rating',            // ✅ YANGI
        'name',
        'who_from',
        'file',
        'date',
        'who_hand',
        'people',
        'comment',
    ];

    // ✅ Date va decimal casting
    protected $casts = [
        'date' => 'date',
        'rating' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ✅ YANGI relationships
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function subtask()
    {
        return $this->belongsTo(SubTask::class);
    }

    // Events
    protected static function booted()
    {
        static::updated(function ($assignment) {
            $user = auth()->user();
        
            // ✅ Yangilangan dirty check - yangi ustunlar qo'shildi
            if ($assignment->isDirty([
                'name', 
                'user_id', 
                'who_from', 
                'file', 
                'date', 
                'who_hand', 
                'people',
                'task_id',      // ✅ YANGI
                'subtask_id',   // ✅ YANGI
                'rating',       // ✅ YANGI
                'comment'       // ✅ YANGI - comment o'zgarganda ham xabarnoma
            ])) {
                // Barcha adminlarga yuborish
                $admins = User::role('Admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new AssignmentUpdatedNotification($assignment, $user));
                }
            }
        });
    }
}