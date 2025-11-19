<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'code',
        'zoho_id',
        'manager_id',
        'head_manager_id',
        'team_leaders',
        'hierarchy',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'team_leaders' => 'array',
        'hierarchy' => 'array',
        'zoho_id' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function headManager()
    {
        return $this->belongsTo(User::class, 'head_manager_id');
    }

    public function teamLeaders()
    {
        return $this->belongsToMany(User::class, 'department_team_leaders', 'department_id', 'user_id');
    }

    public function tasks()
    {
        return $this->hasManyThrough(Task::class, User::class, 'department_id', 'assigned_to');
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        $name = app()->getLocale() === 'ar' ? $this->name_ar : $this->name;
        return $name ?? $this->name ?? '';
    }

    public function getDisplayDescriptionAttribute(): string
    {
        $description = app()->getLocale() === 'ar' ? $this->description_ar : $this->description;
        return $description ?? '';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
