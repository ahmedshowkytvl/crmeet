<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCategoryProperty extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'name_ar',
        'type',
        'options',
        'is_required',
        'sort_order'
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    protected $attributes = [
        'options' => null,
        'is_required' => false,
        'sort_order' => 0,
    ];

    // العلاقات
    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function propertyValues()
    {
        return $this->hasMany(AssetPropertyValue::class, 'property_id');
    }

    // Accessors & Mutators
    public function getDisplayNameAttribute(): string
    {
        $name = app()->getLocale() === 'ar' ? $this->name_ar : $this->name;
        return $name ?? $this->name ?? '';
    }

    public function setOptionsAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['options'] = json_encode(explode("\n", $value));
        } elseif (is_array($value)) {
            $this->attributes['options'] = json_encode($value);
        } else {
            $this->attributes['options'] = null;
        }
    }

    // Scopes
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Methods
    public function getOptionsArray(): array
    {
        return $this->options ?? [];
    }

    public function isSelectType(): bool
    {
        return $this->type === 'select';
    }

    public function isImageType(): bool
    {
        return $this->type === 'image';
    }

    public function isDateType(): bool
    {
        return $this->type === 'date';
    }

    public function isNumberType(): bool
    {
        return $this->type === 'number';
    }

    public function isBooleanType(): bool
    {
        return $this->type === 'boolean';
    }
}
