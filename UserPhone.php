<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPhone extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_type_id',
        'phone_number',
        'country_code',
        'is_primary',
        'is_verified',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_verified' => 'boolean',
    ];

    /**
     * Get the user that owns the phone.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the phone type that owns the phone.
     */
    public function phoneType(): BelongsTo
    {
        return $this->belongsTo(PhoneType::class);
    }

    /**
     * Get the full phone number with country code.
     */
    public function getFullPhoneNumberAttribute(): string
    {
        return $this->country_code . $this->phone_number;
    }

    /**
     * Scope a query to only include primary phones.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope a query to only include verified phones.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
}
