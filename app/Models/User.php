<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasTenant;

    protected $fillable = [
        'agency_id',
        'employer_id',
        'branch_id',
        'name',
        'middle_name',
        'surname',
        'email',
        'contact',
        'username',
        'password',
        'user_type',
        'status',
        'locale',
    ];

    /**
     * The 5 friendly "Access Level" presets the client expects,
     * mapped to the underlying granular roles. Keeps all roles intact.
     */
    public const ACCESS_PRESETS = [
        'super_admin' => 'Super Admin',
        'admin'       => 'Admin',
        'billing'     => 'Accounting',
        'staff'       => 'Receptionist',
        'processor'   => 'Processing',
        'paralegal'   => 'Paralegal',
        'branch'      => 'Branch',
        'operation'   => 'Operation',
    ];

    /** Display label for a user_type (presets first, fall back to raw role). */
    public static function accessLabel(string $userType): string
    {
        return self::ACCESS_PRESETS[$userType] ?? ucwords(str_replace('_', ' ', $userType));
    }

    /**
     * Full name assembled from name + middle name + surname (the "Name" column).
     */
    public function getFullNameAttribute(): string
    {
        return trim(collect([$this->name, $this->middle_name, $this->surname])->filter()->implode(' '));
    }

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * (Branch feature) True when this user is a branch account, i.e. bound to a
     * specific agency branch via branch_id. Branch accounts are auto-scoped to
     * their branch and get a trimmed-down sidebar.
     */
    public function isBranchAccount(): bool
    {
        return (int) $this->branch_id > 0;
    }

    public function permissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    public function activities()
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'locale'            => 'string',
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        ResetPasswordNotification::createUrlUsing(function ($notifiable, $token) {
            return url(route('fra.password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
        });

        ResetPasswordNotification::toMailUsing(function ($notifiable, $token) {
            $url = url(route('fra.password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Reset Your Password — Agency App')
                ->greeting('Hello!')
                ->line('You are receiving this email because we received a password reset request for your account.')
                ->action('Reset Password', $url)
                ->line('This password reset link will expire in ' . config('auth.passwords.' . config('auth.defaults.passwords') . '.expire') . ' minutes.')
                ->line('If you did not request a password reset, no further action is required.');
        });

        $this->notify(new ResetPasswordNotification($token));

        // Reset static callbacks so other models don't inherit
        ResetPasswordNotification::$createUrlCallback = null;
        ResetPasswordNotification::$toMailCallback = null;
    }

    public function isSuperAdmin(): bool
    {
        return $this->user_type === 'super_admin';
    }

    /**
     * True when the user is branch-restricted: a NON-admin account that
     * belongs to a branch. Admins (and super admins) are never locked,
     * even when their account carries a branch_id — they may assign
     * applicants to any branch.
     */
    public function isBranchLocked(): bool
    {
        if ((int) $this->branch_id <= 0) {
            return false;
        }

        return ! in_array($this->user_type, ['admin', 'super_admin'], true);
    }

    public function canImpersonate(): bool
    {
        return $this->isSuperAdmin();
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('user_type', $type);
    }
}
