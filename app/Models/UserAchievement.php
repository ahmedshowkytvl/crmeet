<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'achievement_type',
        'achievement_level',
        'earned_at',
        'metadata'
    ];

    protected $casts = [
        'earned_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Achievement Types
    const TYPE_SPEED_DEMON = 'speed_demon';
    const TYPE_TICKET_MASTER = 'ticket_master';
    const TYPE_CONSISTENCY_KING = 'consistency_king';
    const TYPE_NIGHT_OWL = 'night_owl';

    // Achievement Levels
    const LEVEL_BRONZE = 'bronze';
    const LEVEL_SILVER = 'silver';
    const LEVEL_GOLD = 'gold';
    const LEVEL_PLATINUM = 'platinum';

    /**
     * Get achievement title in Arabic
     */
    public function getTitleAttribute()
    {
        $titles = [
            self::TYPE_SPEED_DEMON => 'شيطان السرعة',
            self::TYPE_TICKET_MASTER => 'سيد التذاكر',
            self::TYPE_CONSISTENCY_KING => 'ملك الثبات',
            self::TYPE_NIGHT_OWL => 'بومة الليل',
        ];

        return $titles[$this->achievement_type] ?? $this->achievement_type;
    }

    /**
     * Get achievement level name in Arabic
     */
    public function getLevelNameAttribute()
    {
        $levels = [
            self::LEVEL_BRONZE => 'برونزي',
            self::LEVEL_SILVER => 'فضي',
            self::LEVEL_GOLD => 'ذهبي',
            self::LEVEL_PLATINUM => 'بلاتيني',
        ];

        return $levels[$this->achievement_level] ?? $this->achievement_level;
    }
}

