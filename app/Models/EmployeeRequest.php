<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'requested_by',
        'manager_id',
        'title',
        'description',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
