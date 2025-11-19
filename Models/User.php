<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasPermissions;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'name_ar',
        'email',
        'username',
        'password',
        'user_type',
        'role_id',
        'profile_picture',
        'hrid',
        'EmployeeCode',
        'work_number',
        'id_number',
        'hire_date',
        'microsoft_teams_id',
        'avaya_extension',
        'address',
        'address_ar',
        'position',
        'position_ar',
        'department_id',
        'branch_id',
        'manager_id',
        'created_by',
        'is_archived',
        'archived_at',
        // Zoho Integration Fields
        'zoho_agent_name',
        'zoho_agent_id',
        'zoho_email',
        'is_zoho_enabled',
        'zoho_linked_at',
        // Legacy fields for backward compatibility
        'phone_work',
        'phone_home',
        'phone_personal',
        'phone_mobile',
        'phone_emergency',
        'whatsapp',
        'telegram',
        'skype',
        'facebook',
        'instagram',
        'job_title',
        'employee_id',
        'hire_date',
        'work_location',
        'office_room',
        'extension',
        'company',
        'work_email',
        'avaya_extension',
        'microsoft_teams_id',
        'office_address',
        'linkedin_url',
        'twitter_url',
        'website_url',
        'birthday',
        'birth_date',
        'nationality',
        'city',
        'country',
        'postal_code',
        'bio',
        'skills',
        'interests',
        'languages',
        'notes',
        'profile_photo',
        'language',
        'timezone',
        'insurance_status',
        'date_format',
        'time_format',
        'notification_preferences',
        'receive_birthday_notifications',
        'show_phone_work',
        'show_phone_personal',
        'show_phone_mobile',
        'show_email',
        'show_address',
        'show_social_media',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthday' => 'date',
            'birth_date' => 'date',
            'hire_date' => 'date',
            'archived_at' => 'datetime',
            'is_archived' => 'boolean',
            'is_zoho_enabled' => 'boolean',
            'zoho_linked_at' => 'datetime',
            'notification_preferences' => 'array',
            'receive_birthday_notifications' => 'boolean',
            'skills' => 'array',
            'interests' => 'array',
            'languages' => 'array',
        ];
    }

    // العلاقات الجديدة
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function phones()
    {
        return $this->hasMany(UserPhone::class);
    }

    public function hiringDocuments()
    {
        return $this->hasMany(HiringDocument::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    // العلاقات القديمة (للتوافق مع الإصدارات السابقة)
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function employeeRequests()
    {
        return $this->hasMany(EmployeeRequest::class, 'employee_id');
    }

    public function requestedBy()
    {
        return $this->hasMany(EmployeeRequest::class, 'requested_by');
    }

    public function managedRequests()
    {
        return $this->hasMany(EmployeeRequest::class, 'approved_by');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function employeeEmails()
    {
        return $this->hasMany(EmployeeEmail::class, 'employee_id');
    }

    public function activeEmails()
    {
        return $this->employeeEmails()->active();
    }

    public function primaryEmail()
    {
        return $this->employeeEmails()->primary();
    }

    // Accessors
    public function getDisplayPositionAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->position_ar : $this->position;
    }

    public function getDisplayAddressAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->address_ar : $this->address;
    }

    public function getPrimaryPhoneAttribute(): ?UserPhone
    {
        return $this->phones()->primary()->first();
    }

    public function getWorkPhoneAttribute(): ?UserPhone
    {
        return $this->phones()->whereHas('phoneType', function($query) {
            $query->where('slug', 'work');
        })->first();
    }

    /**
     * Get all work phones for the user.
     */
    public function workPhones()
    {
        return $this->hasMany(UserPhone::class)->whereHas('phoneType', function($query) {
            $query->where('slug', 'work');
        });
    }

    /**
     * Get the primary work phone.
     */
    public function primaryWorkPhone()
    {
        return $this->workPhones()->where('is_primary', true)->first();
    }

    public function getPersonalPhoneAttribute(): ?UserPhone
    {
        return $this->phones()->whereHas('phoneType', function($query) {
            $query->where('slug', 'personal');
        })->first();
    }

    // Scopes
    public function scopeByRole($query, $roleSlug)
    {
        return $query->whereHas('role', function($q) use ($roleSlug) {
            $q->where('slug', $roleSlug);
        });
    }

    public function scopeManagers($query)
    {
        return $query->byRole('manager');
    }

    public function scopeEmployeeManagers($query)
    {
        return $query->byRole('employee_manager');
    }

    public function scopeEmployees($query)
    {
        return $query->where('user_type', 'employee');
    }

    public function scopeSuppliers($query)
    {
        return $query->where('user_type', 'supplier');
    }

    public function scopeNotArchived($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    public function scopeActiveSuppliers($query)
    {
        return $query->suppliers()->notArchived();
    }

    // Chat System Relations
    public function chatRooms()
    {
        return $this->belongsToMany(ChatRoom::class, 'chat_participants')
                    ->withPivot(['role', 'joined_at', 'last_read_at', 'is_muted', 'is_archived'])
                    ->withTimestamps();
    }

    public function sentMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(ChatMessage::class, 'receiver_id');
    }

    public function chatParticipants()
    {
        return $this->hasMany(ChatParticipant::class);
    }

    // Password Management System Relations
    public function passwordAccounts()
    {
        return $this->belongsToMany(PasswordAccount::class, 'password_assignments', 'user_id', 'account_id')
                    ->withPivot(['access_level', 'can_view_password', 'can_edit_password', 'can_delete_account', 'assigned_at', 'assigned_by'])
                    ->withTimestamps();
    }

    public function passwordAssignments()
    {
        return $this->hasMany(PasswordAssignment::class, 'user_id');
    }

    public function createdPasswordAccounts()
    {
        return $this->hasMany(PasswordAccount::class, 'created_by');
    }

    public function passwordAuditLogs()
    {
        return $this->hasMany(PasswordAuditLog::class, 'user_id');
    }

    public function passwordHistory()
    {
        return $this->hasMany(PasswordHistory::class, 'changed_by');
    }

    // Zoho Integration Relations
    public function zohoStats()
    {
        return $this->hasMany(UserZohoStat::class);
    }

    public function zohoTickets()
    {
        return $this->hasMany(ZohoTicketCache::class);
    }

    public function achievements()
    {
        return $this->hasMany(UserAchievement::class);
    }

    // Zoho Scopes
    public function scopeZohoEnabled($query)
    {
        return $query->where('is_zoho_enabled', true)
                     ->whereNotNull('zoho_agent_name');
    }

    // Employee Scopes
    public function scopeActiveEmployees($query)
    {
        return $query->where('user_type', 'employee')
                     ->where('is_archived', false);
    }

    // Zoho Helpers
    public function hasZohoAccess()
    {
        return $this->is_zoho_enabled && !empty($this->zoho_agent_name);
    }

    public function getLatestZohoStatsAttribute()
    {
        return $this->zohoStats()
                    ->where('period_type', 'monthly')
                    ->where('period_date', now()->startOfMonth())
                    ->first();
    }
}
