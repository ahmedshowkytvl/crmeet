<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'country',
        'website',
        'notes',
        'is_active',
        'is_archived',
        'archived_at',
        'contact_person',
        'contact_phone',
        'contact_email',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
    ];

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNotArchived($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    public function scopeActiveNotArchived($query)
    {
        return $query->active()->notArchived();
    }

    /**
     * Get the notes for the supplier.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(SupplierNote::class)->orderBy('created_at', 'desc');
    }

    /**
     * Alias relationship to avoid conflicts with the `notes` attribute column.
     */
    public function supplierNotes(): HasMany
    {
        return $this->hasMany(SupplierNote::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the latest note for the supplier.
     */
    public function latestNote()
    {
        return $this->hasOne(SupplierNote::class)->latest();
    }
}
