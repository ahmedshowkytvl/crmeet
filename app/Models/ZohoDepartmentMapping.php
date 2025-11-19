<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZohoDepartmentMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'zoho_department_id',
        'zoho_department_name',
        'local_department_id',
        'local_department_name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationship with local department
    public function localDepartment()
    {
        return $this->belongsTo(Department::class, 'local_department_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByZohoId($query, $zohoId)
    {
        return $query->where('zoho_department_id', $zohoId);
    }

    // Static method to get local department ID by Zoho department ID
    public static function getLocalDepartmentId($zohoDepartmentId)
    {
        $mapping = self::active()->byZohoId($zohoDepartmentId)->first();
        return $mapping ? $mapping->local_department_id : null;
    }

    // Static method to get local department name by Zoho department ID
    public static function getLocalDepartmentName($zohoDepartmentId)
    {
        $mapping = self::active()->byZohoId($zohoDepartmentId)->first();
        return $mapping ? $mapping->local_department_name : null;
    }
}
