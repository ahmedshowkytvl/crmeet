<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'address',
        'address_ar',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // العلاقات
    public function assets()
    {
        return $this->hasMany(Asset::class, 'location_id');
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        $name = app()->getLocale() === 'ar' ? $this->name_ar : $this->name;
        return $name ?? $this->name ?? '';
    }

    public function getDisplayAddressAttribute(): string
    {
        $address = app()->getLocale() === 'ar' ? $this->address_ar : $this->address;
        return $address ?? '';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

