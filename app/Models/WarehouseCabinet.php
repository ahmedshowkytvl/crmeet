<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseCabinet extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'cabinet_number',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'location_in_warehouse',
        'total_shelves',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // العلاقات
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function shelves()
    {
        return $this->hasMany(WarehouseShelf::class, 'cabinet_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'cabinet_id');
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

    // Methods
    public function getAvailableShelves()
    {
        return $this->shelves()->active()->get();
    }

    public function getUsagePercentage()
    {
        if ($this->total_shelves == 0) return 0;
        return ($this->shelves()->count() / $this->total_shelves) * 100;
    }

    public function getFullLocationAttribute(): string
    {
        $warehouse = $this->warehouse ? $this->warehouse->display_name : '';
        $location = $this->location_in_warehouse ? " - {$this->location_in_warehouse}" : '';
        return "{$warehouse} - دولاب {$this->cabinet_number}{$location}";
    }
}
