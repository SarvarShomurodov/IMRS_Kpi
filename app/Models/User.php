<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = ['firstName', 'lastName', 'email', 'phone', 'position', 'salary', 'project_id','previous_project_id', 'project_changed_at', 'lastDate', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'deleted_at' => 'datetime',
        'project_changed_at' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (is_null($model->lastDate)) {
                $model->lastDate = now()->toDateString();
            }
        });

        // static::updating(function ($model) {
        //     if ($model->isDirty('project_id') && $model->getOriginal('project_id')) {
        //         $model->previous_project_id = $model->getOriginal('project_id');
        //         $model->project_changed_at = now()->toDateString();
        //     }
        // });
    }

    // Mavjud bog'lanishlar
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    public function previousProject() // 🔥 YANGI
    {
        return $this->belongsTo(Project::class, 'previous_project_id');
    }
    public function taskAssignments()
    {
        return $this->hasMany(TaskAssignment::class, 'user_id')->withTrashed();
    }

    public function bonuses()
    {
        return $this->hasMany(Bonus::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    // Davomat tizimi uchun qo'shilgan bog'lanish
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // Spatie Permission bilan role tekshirish
    public function isAdmin()
    {
        return $this->hasRole('Admin');
    }

    public function isTexnik()
    {
        return $this->hasRole('Texnik');
    }

    // Ism-familyani birlashtirish
    public function getFullNameAttribute()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    // Davomat tizimi uchun name attributeni qo'llab-quvvatlash
    public function getNameAttribute()
    {
        return $this->getFullNameAttribute();
    }
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    // Report.php
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviews()
    {
        return $this->hasMany(ReportReview::class);
    }
    public function canUploadReportFiles()
    {
        $allowedEmails = config('permissions.report_file_upload_allowed_emails', []);
        return in_array($this->email, $allowedEmails);
    }
}
