<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseShelf extends Model
{
    use HasFactory;

    protected $fillable = [
        'cabinet_id',
        'shelf_code',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'capacity',
        'current_usage',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // العلاقات
    public function cabinet()
    {
        return $this->belongsTo(WarehouseCabinet::class, 'cabinet_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'shelf_id');
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

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
                    ->whereRaw('current_usage < capacity');
    }

    // Methods
    public function getUsagePercentage()
    {
        if ($this->capacity == 0) return 0;
        return ($this->current_usage / $this->capacity) * 100;
    }

    public function isAvailable()
    {
        return $this->is_active && $this->current_usage < $this->capacity;
    }

    public function getFullLocationAttribute(): string
    {
        $cabinet = $this->cabinet ? $this->cabinet->cabinet_number : '';
        $warehouse = $this->cabinet && $this->cabinet->warehouse ? $this->cabinet->warehouse->display_name : '';
        return "{$warehouse} - دولاب {$cabinet} - رف {$this->shelf_code}";
    }

    public function incrementUsage()
    {
        $this->increment('current_usage');
    }

    public function decrementUsage()
    {
        if ($this->current_usage > 0) {
            $this->decrement('current_usage');
        }
    }
}
