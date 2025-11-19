<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'address',
        'address_ar',
        'city',
        'city_ar',
        'country',
        'postal_code',
        'phone',
        'email',
        'manager_name',
        'manager_name_ar',
        'manager_id',
        'capacity',
        'capacity_unit',
        'is_active',
        'settings'
    ];

    protected $casts = [
        'capacity' => 'decimal:2',
        'is_active' => 'boolean',
        'settings' => 'array'
    ];

    // العلاقات
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
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

    public function getDisplayAddressAttribute(): string
    {
        $address = app()->getLocale() === 'ar' ? $this->address_ar : $this->address;
        return $address ?? $this->address ?? '';
    }

    public function getDisplayCityAttribute(): string
    {
        $city = app()->getLocale() === 'ar' ? $this->city_ar : $this->city;
        return $city ?? $this->city ?? '';
    }

    public function getDisplayManagerNameAttribute(): string
    {
        $managerName = app()->getLocale() === 'ar' ? $this->manager_name_ar : $this->manager_name;
        return $managerName ?? $this->manager_name ?? '';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Methods
    public function getTotalCapacityUsed()
    {
        return $this->inventory()->sum('quantity_in_stock');
    }

    public function getCapacityPercentage()
    {
        if (!$this->capacity) return 0;
        return ($this->getTotalCapacityUsed() / $this->capacity) * 100;
    }
}
