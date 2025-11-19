<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ContactCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'description',
        'description_en',
        'color',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // العلاقات
    public function contacts()
    {
        // إذا كان جدول contacts موجود وبه عمود contact_type
        if (Schema::hasTable('contacts') && Schema::hasColumn('contacts', 'contact_type')) {
            return $this->hasMany(Contact::class, 'contact_type', 'name');
        }
        
        // إرجاع علاقة فارغة لتجنب الأخطاء
        return $this->hasMany(Contact::class)->whereRaw('1 = 0');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        $name = app()->getLocale() === 'ar' ? $this->name : $this->name_en;
        return $name ?? $this->name ?? '';
    }

    public function getDisplayDescriptionAttribute(): string
    {
        $description = app()->getLocale() === 'ar' ? $this->description : $this->description_en;
        return $description ?? '';
    }

    // Methods
    public function getContactsCount()
    {
        if (Schema::hasTable('contacts') && Schema::hasColumn('contacts', 'contact_type')) {
            return $this->contacts()->count();
        }
        return 0;
    }

    public function getActiveContactsCount()
    {
        if (Schema::hasTable('contacts') && Schema::hasColumn('contacts', 'contact_type')) {
            return $this->contacts()->where('status', 'active')->count();
        }
        return 0;
    }
}
