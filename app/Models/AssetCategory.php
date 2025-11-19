<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'is_active',
        'price'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // العلاقات
    public function assets()
    {
        return $this->hasMany(Asset::class, 'category_id');
    }

    public function properties()
    {
        return $this->hasMany(AssetCategoryProperty::class, 'category_id');
    }

    public function propertiesOrdered()
    {
        return $this->hasMany(AssetCategoryProperty::class, 'category_id')->orderBy('sort_order');
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
}
