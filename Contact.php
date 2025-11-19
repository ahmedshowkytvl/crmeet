<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        // معلومات أساسية
        'name',
        'profile_photo',
        'job_title',
        'department_id',
        'company',
        'contact_type', // مطبعة، مورد، عميل، إلخ
        'description',
        
        // معلومات الاتصال
        'phone_primary',
        'phone_secondary',
        'email_primary',
        'email_secondary',
        'website',
        'address',
        'city',
        'country',
        'postal_code',
        
        // وسائل التواصل الاجتماعي
        'whatsapp',
        'telegram',
        'skype',
        'facebook',
        'instagram',
        'linkedin_url',
        'twitter_url',
        
        // معلومات إضافية
        'notes',
        'rating', // تقييم من 1-5
        'status', // نشط، غير نشط، محظور
        'is_favorite', // مفضل
        'last_contact_date',
        'contact_frequency', // يومي، أسبوعي، شهري، سنوي
        
        // معلومات العمل
        'service_provided', // الخدمة المقدمة
        'price_range', // نطاق الأسعار
        'delivery_time', // وقت التسليم
        'quality_rating', // تقييم الجودة
        'reliability_rating', // تقييم الموثوقية
        
        // إعدادات الخصوصية
        'show_phone_primary',
        'show_phone_secondary',
        'show_email_primary',
        'show_email_secondary',
        'show_address',
        'show_social_media',
    ];

    protected $casts = [
        'last_contact_date' => 'date',
        'is_favorite' => 'boolean',
        'show_phone_primary' => 'boolean',
        'show_phone_secondary' => 'boolean',
        'show_email_primary' => 'boolean',
        'show_email_secondary' => 'boolean',
        'show_address' => 'boolean',
        'show_social_media' => 'boolean',
        'rating' => 'integer',
        'quality_rating' => 'integer',
        'reliability_rating' => 'integer',
    ];

    // العلاقات
    public function contactInteractions()
    {
        return $this->hasMany(ContactInteraction::class);
    }

    public function contactFiles()
    {
        return $this->hasMany(ContactFile::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('contact_type', $type);
    }

    // Accessors
    public function getFullAddressAttribute()
    {
        $address = $this->address;
        if ($this->city) {
            $address .= ', ' . $this->city;
        }
        if ($this->country) {
            $address .= ', ' . $this->country;
        }
        if ($this->postal_code) {
            $address .= ' ' . $this->postal_code;
        }
        return $address;
    }

    public function getDisplayNameAttribute()
    {
        if ($this->company) {
            return $this->company . ' - ' . $this->name;
        }
        return $this->name;
    }

    // Methods
    public function getPrimaryPhone()
    {
        return $this->phone_primary ?: $this->phone_secondary;
    }

    public function getPrimaryEmail()
    {
        return $this->email_primary ?: $this->email_secondary;
    }

    public function getAverageRating()
    {
        return ($this->rating + $this->quality_rating + $this->reliability_rating) / 3;
    }

    public function updateLastContact()
    {
        $this->update(['last_contact_date' => now()]);
    }
}
