<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'code',
        'address',
        'address_ar',
        'city',
        'city_ar',
        'country',
        'country_ar',
        'postal_code',
        'phone',
        'email',
        'manager_name',
        'manager_name_ar',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the users for the branch.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the display name based on current locale.
     */
    public function getDisplayNameAttribute(): string
    {
        $name = app()->getLocale() === 'ar' ? $this->name_ar : $this->name;
        return $name ?? $this->name ?? '';
    }

    /**
     * Get the display description based on current locale.
     */
    public function getDisplayDescriptionAttribute(): string
    {
        $description = app()->getLocale() === 'ar' ? $this->description_ar : $this->description;
        return $description ?? '';
    }

    /**
     * Get the display address based on current locale.
     */
    public function getDisplayAddressAttribute(): string
    {
        $address = app()->getLocale() === 'ar' ? $this->address_ar : $this->address;
        return $address ?? '';
    }

    /**
     * Get the display city based on current locale.
     */
    public function getDisplayCityAttribute(): string
    {
        $city = app()->getLocale() === 'ar' ? $this->city_ar : $this->city;
        return $city ?? '';
    }

    /**
     * Get the display country based on current locale.
     */
    public function getDisplayCountryAttribute(): string
    {
        $country = app()->getLocale() === 'ar' ? $this->country_ar : $this->country;
        return $country ?? '';
    }

    /**
     * Get the display manager name based on current locale.
     */
    public function getDisplayManagerNameAttribute(): string
    {
        $managerName = app()->getLocale() === 'ar' ? $this->manager_name_ar : $this->manager_name;
        return $managerName ?? '';
    }

    /**
     * Scope a query to only include active branches.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order branches by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
