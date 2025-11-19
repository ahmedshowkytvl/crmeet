<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'enabled',
        'sound_enabled',
        'browser_enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'sound_enabled' => 'boolean',
        'browser_enabled' => 'boolean',
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * الحصول على تفضيلات المستخدم أو إنشاء الافتراضية
     */
    public static function getOrCreate(int $userId, string $type): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId, 'type' => $type],
            [
                'enabled' => true,
                'sound_enabled' => true,
                'browser_enabled' => true,
            ]
        );
    }

    /**
     * الحصول على جميع تفضيلات المستخدم
     */
    public static function getAllForUser(int $userId): array
    {
        $preferences = static::where('user_id', $userId)->get()->keyBy('type');
        
        $types = ['message', 'task', 'asset'];
        $result = [];

        foreach ($types as $type) {
            $result[$type] = $preferences[$type] ?? (object)[
                'enabled' => true,
                'sound_enabled' => true,
                'browser_enabled' => true,
            ];
        }

        return $result;
    }
}

