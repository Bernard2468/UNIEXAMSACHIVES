<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'is_admin',
        'is_approve',
        'role',
        'super_admin_access_granted_at',
        'super_admin_granted_by',
        'profile_picture',
        'department_id',
        'staff_category',
        'position_id',
        'password_changed',
        'admin_access_requested',
        'admin_access_reason',
        'admin_access_supervisor',
        'admin_access_supervisor_email',
        'admin_access_requested_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'super_admin_access_granted_at' => 'datetime',
            'admin_access_requested_at' => 'datetime',
        ];
    }

    public function messages()
    {
        return $this->belongsToMany(Message::class, 'message_user_read')
                    ->withPivot('is_read')
                    ->withTimestamps();
    }

    /**
     * Get the profile picture URL
     */
    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture) {
            return asset('profile_pictures/' . $this->profile_picture);
        }
        return asset('profile_pictures/default-profile.png'); // Default profile picture
    }

    /**
     * Get the department that the user belongs to
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the position that the user belongs to
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Check if user needs to change their temporary password
     */
    public function needsPasswordChange()
    {
        return !$this->password_changed;
    }

    /**
     * Check if user is using a temporary password
     */
    public function isUsingTemporaryPassword()
    {
        return $this->is_approve && !$this->password_changed;
    }

    // ===== Super Admin System Relationships =====
    
    /**
     * User who granted super admin access to this user
     */
    public function superAdminGrantor()
    {
        return $this->belongsTo(User::class, 'super_admin_granted_by');
    }

    /**
     * System subscriptions created by this user
     */
    public function createdSubscriptions()
    {
        return $this->hasMany(SystemSubscription::class, 'created_by');
    }

    /**
     * System subscriptions updated by this user
     */
    public function updatedSubscriptions()
    {
        return $this->hasMany(SystemSubscription::class, 'updated_by');
    }

    /**
     * Payment transactions initiated by this user
     */
    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'user_id');
    }

    /**
     * Payment transactions processed by this user
     */
    public function processedTransactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'processed_by');
    }

    /**
     * Maintenance logs performed by this user
     */
    public function maintenanceLogs()
    {
        return $this->hasMany(SystemMaintenanceLog::class, 'performed_by');
    }

    /**
     * System notifications created by this user
     */
    public function createdNotifications()
    {
        return $this->hasMany(SystemNotification::class, 'created_by');
    }

    /**
     * Notification reads by this user
     */
    public function notificationReads()
    {
        return $this->hasMany(UserNotificationRead::class, 'user_id');
    }

    /**
     * System notifications read by this user
     */
    public function readNotifications()
    {
        return $this->belongsToMany(SystemNotification::class, 'user_notification_reads', 'user_id', 'system_notification_id')
                    ->withPivot(['read_at', 'dismissed_at', 'acknowledged_at'])
                    ->withTimestamps();
    }

    // ===== Role Check Methods =====
    // 
    // ⚠️ IMPORTANT: This system uses REVERSED role terminology in the UI!
    // 
    // Database values:
    //   - 'super_admin' = Super Admin (unchanged)
    //   - 'admin' = Normal users (displayed as "User" in UI)
    //   - 'user' = Administrators (displayed as "Admin" in UI)
    // 
    // See ROLE_TERMINOLOGY.md for complete documentation.
    
    /**
     * Check if user is a super admin
     * 
     * @return bool True if role is 'super_admin'
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is an admin (normal user in UI terms)
     * 
     * Note: Database 'admin' = UI "User" (normal users)
     * 
     * @return bool True if role is 'admin' or 'super_admin'
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->isSuperAdmin();
    }

    /**
     * Check if user is a regular user (admin in UI terms)
     * 
     * Note: Database 'user' = UI "Admin" (system administrators)
     * This method checks for users who can manage subscriptions and make payments.
     * 
     * @return bool True if role is 'user'
     */
    public function isRegularUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if user has admin or super admin privileges
     */
    public function hasAdminPrivileges(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayAttribute(): string
    {
        return match($this->role) {
            'super_admin' => 'Super Administrator',
            'admin' => 'Administrator',
            'user' => 'User',
            default => ucfirst($this->role ?? 'User')
        };
    }

    /**
     * Get role badge color
     */
    public function getRoleBadgeColorAttribute(): string
    {
        return match($this->role) {
            'super_admin' => 'danger',
            'admin' => 'warning',
            'user' => 'info',
            default => 'secondary'
        };
    }

    /**
     * Grant super admin access to this user
     */
    public function grantSuperAdminAccess(int $grantedBy): void
    {
        $this->update([
            'role' => 'super_admin',
            'super_admin_access_granted_at' => now(),
            'super_admin_granted_by' => $grantedBy,
        ]);
    }

    /**
     * Revoke super admin access from this user
     */
    public function revokeSuperAdminAccess(): void
    {
        $this->update([
            'role' => $this->is_admin ? 'admin' : 'user',
            'super_admin_access_granted_at' => null,
            'super_admin_granted_by' => null,
        ]);
    }

    /**
     * Get unread system notifications count for this user
     */
    public function getUnreadSystemNotificationsCount(): int
    {
        return SystemNotification::active()
            ->forUser($this->id)
            ->unreadByUser($this->id)
            ->count();
    }

    /**
     * Get active system notifications for this user
     */
    public function getActiveSystemNotifications()
    {
        return SystemNotification::active()
            ->forUser($this->id)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get memos bookmarked by this user
     */
    public function bookmarkedMemos()
    {
        return $this->belongsToMany(EmailCampaign::class, 'memo_user_bookmarks', 'user_id', 'campaign_id')
                    ->withTimestamps()
                    ->orderBy('memo_user_bookmarks.created_at', 'desc');
    }

    /**
     * Get the committees/boards that this user belongs to
     */
    public function committees()
    {
        return $this->belongsToMany(Committee::class, 'committee_user')
                    ->withTimestamps();
    }

    /**
     * Get active committees/boards that this user belongs to
     */
    public function activeCommittees()
    {
        return $this->committees()->where('status', 'active');
    }
}
