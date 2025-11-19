<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetPropertyValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'property_id',
        'value'
    ];

    // العلاقات
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function property()
    {
        return $this->belongsTo(AssetCategoryProperty::class, 'property_id');
    }

    // Methods
    public function getFormattedValueAttribute(): string
    {
        if (empty($this->value)) {
            return '';
        }

        return match($this->property->type) {
            'boolean' => $this->value ? __('Yes') : __('No'),
            'date' => $this->value ? \Carbon\Carbon::parse($this->value)->format('Y-m-d') : '',
            'number' => is_numeric($this->value) ? number_format($this->value) : $this->value,
            default => $this->value
        };
    }
}

