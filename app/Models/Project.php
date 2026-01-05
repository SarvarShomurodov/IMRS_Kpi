<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description'];
    
    // ✅ Agar kerak bo'lsa qo'shing
    protected $dates = ['deleted_at']; // Eski Laravel uchun
    
    // yoki yangi Laravel uchun
    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'project_id');
    }
    
    // ✅ Faol userlar uchun
    public function activeUsers()
    {
        return $this->hasMany(User::class, 'project_id');
    }
    
    // ✅ Barcha userlar (o'chirilganlar bilan)
    public function allUsers()
    {
        return $this->hasMany(User::class, 'project_id')->withTrashed();
    }
}