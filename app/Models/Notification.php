<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'actor_id',
        'resource_type',
        'resource_id',
        'link',
        'metadata',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'time_ago',
        'actor_name',
        'actor_avatar',
        'localized_title',
        'localized_body',
    ];

    /**
     * العلاقة مع المستخدم المستلم
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * العلاقة مع المستخدم الفاعل
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Scope للإشعارات غير المقروءة
     * إشعار غير مقروء إذا: (is_read = false أو is_read IS NULL) AND read_at IS NULL
     */
    public function scopeUnread($query)
    {
        return $query->where(function($q) {
            // is_read يجب أن يكون false أو NULL (أي ليس true)
            $q->where('is_read', false)
              ->orWhereNull('is_read');
        })
        // و read_at يجب أن يكون NULL (لم يُقرأ بعد)
        ->whereNull('read_at');
    }

    /**
     * Scope للإشعارات المقروءة
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope حسب النوع
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope حسب المستخدم
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * تحديد الإشعار كمقروء
     */
    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return false;
        }

        return $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * الوقت النسبي
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * اسم الفاعل - يعتمد على locale الحالي
     * Note: Frontend will handle language switching, this is a fallback
     */
    public function getActorNameAttribute(): ?string
    {
        if (!$this->actor) {
            return null;
        }

        // Use current locale to determine which name to show
        $locale = app()->getLocale();
        if ($locale === 'ar') {
            return $this->actor->name_ar ?: $this->actor->name;
        } else {
            return $this->actor->name ?: $this->actor->name_ar;
        }
    }

    /**
     * صورة الفاعل
     */
    public function getActorAvatarAttribute(): ?string
    {
        return $this->actor?->profile_picture;
    }

    /**
     * العنوان باللغة الحالية
     */
    public function getLocalizedTitleAttribute(): string
    {
        $locale = app()->getLocale();
        
        // For birthday notifications, check metadata for translations
        if ($this->type === 'birthday' && $this->metadata) {
            $titleAr = $this->metadata['title_ar'] ?? null;
            $titleEn = $this->metadata['title_en'] ?? null;
            
            if ($locale === 'ar' && $titleAr) {
                return $titleAr;
            } elseif ($locale === 'en' && $titleEn) {
                return $titleEn;
            }
        }
        
        // Fallback to stored title
        return $this->title;
    }

    /**
     * النص باللغة الحالية
     */
    public function getLocalizedBodyAttribute(): string
    {
        $locale = app()->getLocale();
        
        // For birthday notifications, check metadata for translations
        if ($this->type === 'birthday' && $this->metadata) {
            $bodyAr = $this->metadata['body_ar'] ?? null;
            $bodyEn = $this->metadata['body_en'] ?? null;
            
            if ($locale === 'ar' && $bodyAr) {
                return $bodyAr;
            } elseif ($locale === 'en' && $bodyEn) {
                return $bodyEn;
            }
        }
        
        // Fallback to stored body
        return $this->body;
    }

    /**
     * أيقونة حسب النوع
     */
    public function getIconAttribute(): string
    {
        return match($this->type) {
            'message' => 'fas fa-envelope',
            'task' => 'fas fa-tasks',
            'asset' => 'fas fa-mobile-alt',
            'birthday' => 'fas fa-birthday-cake',
            default => 'fas fa-bell',
        };
    }

    /**
     * لون حسب النوع
     */
    public function getColorAttribute(): string
    {
        return match($this->type) {
            'message' => 'text-blue-500',
            'task' => 'text-green-500',
            'asset' => 'text-yellow-500',
            'birthday' => 'text-pink-500',
            default => 'text-gray-500',
        };
    }
}

