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
     * Whether this user may access a module (see App\Support\ModuleAccess).
     * super_admin/admin => everything; Accounting (billing) => admin minus the
     * denied modules; everyone else => the allowlisted modules only.
     */
    public function canAccessModule(string $module): bool
    {
        return \App\Support\ModuleAccess::allows($this->user_type, $module);
    }

    /** True for the limited "Rest of Account" tier (everyone below admin/accounting). */
    public function isRestOfAccount(): bool
    {
        return \App\Support\ModuleAccess::isRestOfAccount($this->user_type);
    }

    /**
     * Privileged "sees everything" account (client 2026-09-28: MAE, EVELYN,
     * ANGEL). These bypass the own-records-only scoping and keep the gated
     * folders / Statistics report / full Reports tab / Agent Deduction create.
     * Configured in config/mjolnir.php.
     */
    public function isPrivileged(): bool
    {
        $list = (array) config('mjolnir.privileged_accounts', []);
        if (empty($list)) {
            return false;
        }

        $name  = strtoupper(trim((string) $this->name));
        $email = strtoupper(trim((string) strtok((string) $this->email, '@')));

        return in_array($name, $list, true) || in_array($email, $list, true);
    }

    /**
     * Whether this user may open the Backout / Cancelled / Repat folder.
     * (Mjolnir card "Backout, Repat Module") Admin + Accounting only.
     */
    public function canViewBackoutRepat(): bool
    {
        return in_array((string) $this->user_type, ['super_admin', 'admin', 'billing'], true);
    }

    /**
     * Whether this user may change expense-request status (single or bulk).
     * (Cyd 2026-09-28 #1) Admins plus the privileged accounts (Mae/Evelyn).
     */
    public function canChangeExpenseStatus(): bool
    {
        return in_array((string) $this->user_type, ['super_admin', 'admin'], true)
            || $this->isPrivileged();
    }

    /**
     * Whether this user may see accounting data belonging to other users.
     * Admins/Accounting keep full visibility; everyone else is limited to
     * their own records unless they are a privileged account.
     */
    public function seesAllAccountingData(): bool
    {
        if ($this->isPrivileged()) {
            return true;
        }

        // (Cyd 2026-09-28 #4) Only *Rest of Account* (staff) is limited to
        // their own records. Admin AND Accounting (billing) keep full visibility.
        return in_array((string) $this->user_type, ['super_admin', 'admin', 'billing'], true);
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

    /**
     * True when this account belongs to the agency's MAIN OFFICE branch.
     * Main Office users are head-office staff: they may pick any branch on
     * forms that support it (e.g. Expense requests) instead of being locked
     * to their own branch. (Cyd 2026-09-26)
     */
    public function isMainOffice(): bool
    {
        if ((int) $this->branch_id <= 0) {
            return false;
        }

        $name = optional($this->branch)->name;

        return $name !== null && mb_strtolower(trim($name)) === 'main office';
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
     * Full-access agency admin (super_admin or admin).
     * Used to gate the FRA (employer) Delete action — Accounting and
     * Rest-of-Account do not see it. (Mjolnir card "FRA Module")
     */
    public function isAdmin(): bool
    {
        return in_array((string) $this->user_type, ['super_admin', 'admin'], true);
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

        // Any account assigned to a branch is a branch account — admin or not.
        // Only super_admin (system-wide) keeps the free branch dropdown.
        return $this->user_type !== 'super_admin';
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
