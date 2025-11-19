<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PasswordCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'icon',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the password accounts for this category.
     */
    public function passwordAccounts(): HasMany
    {
        return $this->hasMany(PasswordAccount::class, 'category_id');
    }

    /**
     * Get the display name based on locale.
     */
    public function getDisplayNameAttribute(): string
    {
        $name = app()->getLocale() === 'ar' && $this->name_ar 
            ? $this->name_ar 
            : $this->name;
            
        return $name ?? '';
    }

    /**
     * Get the display description based on locale.
     */
    public function getDisplayDescriptionAttribute(): string
    {
        $description = app()->getLocale() === 'ar' && $this->description_ar 
            ? $this->description_ar 
            : $this->description;
            
        return $description ?? '';
    }

    /**
     * Scope for active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered categories.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
