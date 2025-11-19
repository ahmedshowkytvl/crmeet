<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_code',
        'barcode',
        'name',
        'name_ar',
        'category_id',
        'serial_number',
        'description',
        'description_ar',
        'purchase_date',
        'warranty_expiry',
        'cost',
        'status',
        'location_id',
        'assigned_to',
        'barcode_image',
        'store_code',
        'price',
        'currency',
        'warehouse_id',
        'inventory_status',
        'quantity',
        'current_cabinet_id',
        'current_shelf_id',
        'current_availability_status',
        'current_location_description',
        'last_movement_at'
    ];

    protected $casts = [
        'purchase_date' => 'date:d/m/Y',
        'warranty_expiry' => 'date:d/m/Y',
        'cost' => 'decimal:2',
        'price' => 'decimal:2',
        'last_movement_at' => 'datetime',
    ];

    // العلاقات
    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'location_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class, 'asset_id');
    }

    public function logs()
    {
        return $this->hasMany(AssetLog::class, 'asset_id');
    }

    public function propertyValues()
    {
        return $this->hasMany(AssetPropertyValue::class, 'asset_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function currentCabinet()
    {
        return $this->belongsTo(WarehouseCabinet::class, 'current_cabinet_id');
    }

    public function currentShelf()
    {
        return $this->belongsTo(WarehouseShelf::class, 'current_shelf_id');
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

    public function getInventoryStatusLabelAttribute(): string
    {
        $statuses = [
            'in_stock' => __('assets.in_stock') ?: (app()->getLocale() == 'ar' ? 'في المخزون' : 'In Stock'),
            'out_of_stock' => __('assets.out_of_stock') ?: (app()->getLocale() == 'ar' ? 'نفد من المخزون' : 'Out of Stock'),
            'low_stock' => __('assets.low_stock') ?: (app()->getLocale() == 'ar' ? 'مخزون منخفض' : 'Low Stock'),
            'reserved' => __('assets.reserved') ?: (app()->getLocale() == 'ar' ? 'محجوز' : 'Reserved'),
            'damaged' => __('assets.damaged') ?: (app()->getLocale() == 'ar' ? 'تالف' : 'Damaged'),
            'expired' => __('assets.expired') ?: (app()->getLocale() == 'ar' ? 'منتهي الصلاحية' : 'Expired'),
        ];

        return $statuses[$this->inventory_status] ?? ($this->inventory_status ?: (app()->getLocale() == 'ar' ? 'غير محدد' : 'Not Specified'));
    }

    public function getFormattedPriceAttribute(): string
    {
        if (!$this->price) return (app()->getLocale() == 'ar' ? 'غير محدد' : 'Not Specified');
        
        $currencies = [
            'EGP' => 'ج.م',
            'USD' => '$',
            'EUR' => '€',
            'SAR' => 'ر.س',
            'AED' => 'د.إ'
        ];

        $symbol = $currencies[$this->currency] ?? $this->currency;
        return number_format($this->price, 2) . ' ' . $symbol;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active' => __('assets.active'),
            'maintenance' => __('assets.maintenance'),
            'retired' => __('assets.retired'),
            default => $this->status
        };
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    public function scopeRetired($query)
    {
        return $query->where('status', 'retired');
    }

    public function scopeAssigned($query)
    {
        return $query->whereNotNull('assigned_to');
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    // Methods
    public function generateAssetCode(): string
    {
        $prefix = 'AST';
        $year = date('Y');
        $month = date('m');
        $random = strtoupper(Str::random(4));
        
        return "{$prefix}-{$year}{$month}-{$random}";
    }

    public function generateBarcode(): string
    {
        return $this->asset_code;
    }

    public function getPropertyValue($propertyId)
    {
        return $this->propertyValues()->where('property_id', $propertyId)->first()?->value;
    }

    public function setPropertyValue($propertyId, $value)
    {
        return $this->propertyValues()->updateOrCreate(
            ['property_id' => $propertyId],
            ['value' => $value]
        );
    }
}

