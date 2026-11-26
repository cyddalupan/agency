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
        // FINAS access roles (Mjolnir card "FINAS: User Access", 2026-10-20).
        'recruitment' => 'Recruitment',
        'welfare'     => 'Welfare',
        'staff'       => 'Receptionist',
        'processor'   => 'Processing',
        'paralegal'   => 'Paralegal',
        'branch'      => 'Branch',
        'operation'   => 'Operation',
        // (Mjolnir card "User Access Level 'Encoder'", 2026-10-08) Narrowest
        // tier: can ONLY reach the Dashboard + Applicant module.
        'encoder'     => 'Encoder',
        // (Card 6af81c30, 2026-11-14) FINAS/CSDI access level limited to
        // Dashboard, Company Profile, Employee, Applicant, Backout/Cancelled/
        // Repat, Welfare, FRA and Report. Offered on those two tenants only.
        'dmw_user'    => 'DMW User',
        // FRA portal account (Mjolnir card "FINAS: FRA Feature", 2026-10-20).
        // Logs into /fra only, tied to one employer (FRA) record, and sees
        // that employer's applicants exclusively.
        'employer'    => 'FRA (Employer)',
        // QMS-09 (card 6afa0da6eb58094f7e27f315): document control / audit
        // roles from spec §19. Additive only — no existing role is renumbered
        // or renamed, and both are appended so stored user_type values are
        // untouched.
        'qms_manager'      => 'QMS Manager',
        'internal_auditor' => 'Internal Auditor',
    ];

    /** The two QMS roles (QMS-09). */
    public const QMS_ROLES = ['qms_manager', 'internal_auditor'];

    /** True for FRA portal accounts (user_type = employer). */
    public function isFraAccount(): bool
    {
        return (string) $this->user_type === 'employer';
    }

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
    /** Whether this user's agency offers the EMPLOYEE module (CSDI / FINAS). */
    public function employeeModuleEnabled(): bool
    {
        return \App\Support\ModuleAccess::employeeEnabled($this->agency_id);
    }

    public function canAccessModule(string $module): bool
    {
        // $this->id is passed so explicit per-user grants (config
        // mjolnir.agent_report_user_grants) can be honoured — see the yves
        // card, Cyd 2026-11-26.
        return \App\Support\ModuleAccess::allows($this->user_type, $module, $this->agency_id, (int) $this->id);
    }

    /**
     * Case / Welfare / Accounting are view-only for Recruitment and Branch
     * accounts (FINAS card: "Case, Welfare & Accounting - view only, no add
     * transaction"). Welfare itself keeps full Case/Welfare access.
     */
    public function isApplicantSubModuleReadOnly(string $type): bool
    {
        if (! in_array((string) $this->user_type, ['recruitment', 'branch'], true)) {
            return false;
        }

        return in_array($type, ['cases', 'welfare', 'accounting'], true);
    }

    /**
     * FINAS card (2026-11-06): the applicant's WELFARE tab is only visible to
     * Admin and Welfare accounts.
     */
    public function canAccessWelfareTab(): bool
    {
        return in_array((string) $this->user_type, ['super_admin', 'admin', 'welfare'], true);
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
     * Whether this user may edit Receivable and Expenses & Payments
     * transactions (Mjolnir card "Receivable and Expenses & Payment Editable").
     * Admin (super_admin / admin) + Accounting (billing) only.
     */
    public function canEditTransactions(): bool
    {
        return in_array((string) $this->user_type, ['super_admin', 'admin', 'billing'], true);
    }

    /**
     * Whether this user may edit THIS record (Mjolnir card "Upload and Edit",
     * Cyd 2026-11-09): "All Requestor should be able to edit their request as
     * long as the status is Pending. Only the user can edit their request and
     * admin and accounting."
     *
     * Admin / Accounting keep editing anything at any status. Everyone else may
     * edit ONLY records they raised, and ONLY while those are pending.
     */
    public function canEditTransaction($record): bool
    {
        if ($this->canEditTransactions()) {
            return true;
        }

        if (! $record) {
            return false;
        }

        // Owner check: both modules carry the requestor in user_id.
        if ((int) ($record->user_id ?? 0) !== (int) $this->id) {
            return false;
        }

        return (string) ($record->status ?? '') === 'pending';
    }

    /**
     * Human-readable reason an edit was refused, so the controllers can return
     * a useful 403 instead of one generic message.
     */
    public function transactionEditRefusal($record): string
    {
        if (! $record) {
            return 'This transaction could not be found.';
        }

        if ((int) ($record->user_id ?? 0) !== (int) $this->id) {
            return 'You can only edit your own request.';
        }

        return 'Only Pending requests can be edited.';
    }

    /**
     * Whether this user may see the money-total summary cards on the
     * Receivable and Expenses & Payments dashboards (Josh, 2026-11-08):
     * only Admin (super_admin / admin) + Accounting (billing). Everyone else
     * keeps the modules but sees only the count cards.
     */
    public function canSeeFinancialTotals(): bool
    {
        return in_array((string) $this->user_type, ['super_admin', 'admin', 'billing'], true);
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
        if (! self::branchRestrictionsEnabled()) {
            return false;
        }

        return (int) $this->branch_id > 0;
    }

    /**
     * Global kill-switch for the Branch feature (see config/mjolnir.php).
     * TRUE = branch restrictions apply (normal). FALSE = temporary lift.
     */
    public static function branchRestrictionsEnabled(): bool
    {
        return (bool) config('mjolnir.branch_restrictions', true);
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
        if ($name === null) {
            return false;
        }

        // Agencies label the head office differently ("Main Office",
        // "mAIN BRANCH", ...). Both spellings mean head office, so head-office
        // accounts are never treated as branch-restricted. (Cyd 2026-10-23)
        $normalized = mb_strtolower(preg_replace('/\s+/', ' ', trim($name)));

        return in_array($normalized, ['main office', 'main branch'], true);
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
     * True when the user is branch-restricted: ANY account bound to a branch
     * (admin included) is locked to that branch. Only super_admin is exempt,
     * and only because it is system-wide; branch-less accounts (main office)
     * are unlocked by the branch_id check below.
     *
     * The admin exemption was removed in c0125113 (2026-09-05). Prose written
     * before that still claims admins are never locked with a branch_id — it
     * is stale, do not implement from it.
     */
    public function isBranchLocked(): bool
    {
        // Temporary kill-switch (Mjolnir/Cyd 2026-10-14): when branch
        // restrictions are disabled nobody is branch-locked, so branch
        // accounts can view/edit records for any branch.
        if (! self::branchRestrictionsEnabled()) {
            return false;
        }

        if ((int) $this->branch_id <= 0) {
            return false;
        }

        // (Cyd 2026-10-23) Head-office ("Main Office" / "Main Branch") accounts
        // are never branch-restricted: they see every branch's agents and
        // applicants. Only *branch* accounts get scoped.
        if ($this->isMainOffice()) {
            return false;
        }

        // Any other account assigned to a branch is a branch account — admin
        // or not. Only super_admin (system-wide) keeps the free branch dropdown.
        return $this->user_type !== 'super_admin';
    }

    /**
     * Main Office accounts that must see only Main Office agents in the Agent
     * Ledger (Cyd 2026-11-26).
     *
     * These accounts sit on a branch literally named "Main Office", which
     * normal head-office handling treats as "never branch-restricted" — so
     * without this they see every branch's agents. The exception is stated as
     * an explicit id allowlist on purpose: it must not be inferred from
     * user_type or branch name, because head office genuinely means
     * unrestricted for every other account and every other module.
     *
     * yves (273) was added by the sibling "grant yves access" card (Cyd
     * 2026-11-26) in the same release as his module grant: access without this
     * scoping would have shown him all 429 agents. Keep this list short.
     */
    public function isAgentLedgerMainOfficeOnly(): bool
    {
        return in_array((int) $this->id, self::AGENT_LEDGER_MAIN_OFFICE_ONLY_IDS, true);
    }

    /** Explicit user ids for isAgentLedgerMainOfficeOnly(). */
    public const AGENT_LEDGER_MAIN_OFFICE_ONLY_IDS = [270, 274, 273];

    public function canImpersonate(): bool
    {
        return $this->isSuperAdmin();
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('user_type', $type);
    }
}
