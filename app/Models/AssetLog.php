<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'action',
        'user_id',
        'date',
        'notes',
        'metadata',
        'cabinet_id',
        'shelf_id',
        'assigned_to_user',
        'availability_status',
        'location_description',
        'action_timestamp'
    ];

    protected $casts = [
        'date' => 'date',
        'metadata' => 'array',
        'action_timestamp' => 'datetime',
    ];

    // العلاقات
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cabinet()
    {
        return $this->belongsTo(WarehouseCabinet::class, 'cabinet_id');
    }

    public function shelf()
    {
        return $this->belongsTo(WarehouseShelf::class, 'shelf_id');
    }

    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'assigned_to_user');
    }

    // Scopes
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }

    // Methods
    public function getActionLabelAttribute(): string
    {
        $actions = [
            'stored' => app()->getLocale() == 'ar' ? 'مخزن' : 'Stored',
            'checked_out' => app()->getLocale() == 'ar' ? 'تم تسليمه' : 'Checked Out',
            'returned' => app()->getLocale() == 'ar' ? 'تم إرجاعه' : 'Returned',
            'moved' => app()->getLocale() == 'ar' ? 'تم نقله' : 'Moved',
            'maintenance' => app()->getLocale() == 'ar' ? 'صيانة' : 'Maintenance',
            'disposed' => app()->getLocale() == 'ar' ? 'تم التصرف فيه' : 'Disposed',
            'created' => app()->getLocale() == 'ar' ? 'تم إنشاؤه' : 'Created',
            'updated' => app()->getLocale() == 'ar' ? 'تم تحديثه' : 'Updated',
            'assigned' => app()->getLocale() == 'ar' ? 'تم تعيينه' : 'Assigned',
            'repaired' => app()->getLocale() == 'ar' ? 'تم إصلاحه' : 'Repaired',
        ];

        return $actions[$this->action] ?? $this->action;
    }

    public function getAvailabilityStatusLabelAttribute(): string
    {
        return match($this->availability_status) {
            'available' => __('متاح في المخزن'),
            'checked_out' => __('مع موظف'),
            'in_use' => __('غير متاح مؤقتاً'),
            'maintenance' => __('في الصيانة'),
            'disposed' => __('تم التصرف فيه'),
            default => $this->availability_status
        };
    }

    public function getLocationDescriptionAttribute(): string
    {
        if ($this->location_description) {
            return $this->location_description;
        }

        if ($this->cabinet && $this->shelf) {
            return "دولاب {$this->cabinet->cabinet_number} - رف {$this->shelf->shelf_code}";
        } elseif ($this->cabinet) {
            return "دولاب {$this->cabinet->cabinet_number}";
        }

        return __('غير محدد');
    }
}

