<?php

namespace App\Support;

/**
 * Central access map for web (agency) users.
 *
 * Three access tiers beyond the full-access roles (super_admin/admin):
 *
 *  - Accounting (`billing`): full admin access MINUS ACCOUNTING_DENIED.
 *  - "Rest of Account" (every other user_type): only STAFF_ALLOWED modules.
 *  - Encoder (`encoder`): ONLY the Dashboard + Applicant module (ENCODER_ALLOWED).
 *
 * Introduced 2026-09-24 per client request (Cyd). Kept here so the middleware,
 * the sidebar, and the User model all read from one source of truth.
 * Encoder tier added 2026-10-08 (Mjolnir card "User Access Level 'Encoder'").
 */
class ModuleAccess
{
    /** Modules Accounting (billing) must NOT access. */
    public const ACCOUNTING_DENIED = [
        'users',
        'custom-fields',
        'settings',
        'status-codes',
        'languages',
        'skills',
        'report-templates',
    ];

    /**
     * Modules the Encoder tier may access — Dashboard + Applicant module only
     * (Mjolnir card "User Access Level 'Encoder'", 2026-10-08). The infra
     * entries (notifications/ai/logout/storage) are app chrome, not business
     * modules; everything else (FRA, Reports, Receivable, Expense, Agents
     * Report, Users, Settings, ...) is 403 and hidden from the sidebar.
     */
    public const ENCODER_ALLOWED = [
        'dashboard',
        'notifications',
        'ai',
        'logout',
        'storage',
        'applicants',
    ];

    /**
     * FINAS access roles (Mjolnir card "FINAS: User Access", 2026-10-20).
     *
     * Recruitment: Applicants + FRA + Reference Data + Lookups. Case,
     * Welfare and Accounting inside the applicant are view-only (no add/edit).
     */
    public const RECRUITMENT_ALLOWED = [
        'dashboard',
        'notifications',
        'ai',
        'logout',
        'storage',
        'api',
        'applicants',
        'employers',
        'branches',
        'countries',
        'positions',
        'status-codes',
        'languages',
        'skills',
    ];

    /**
     * Branch: same as Recruitment EXCEPT Reference Data and Lookups
     * (no branches/countries/positions/status-codes/languages/skills).
     */
    public const BRANCH_ALLOWED = [
        'dashboard',
        'notifications',
        'ai',
        'logout',
        'storage',
        'api',
        'applicants',
        'employers',
        // (Cyd 2026-10-23) Agents Report is shown to every user; branch
        // accounts only see the agents registered under their own branch.
        'agent_report',
    ];

    /**
     * Welfare: Applicants only, with full Case & Welfare tabs.
     * No FRA, Reports, Accounting, Reference Data or Lookups.
     */
    public const WELFARE_ALLOWED = [
        'dashboard',
        'notifications',
        'ai',
        'logout',
        'storage',
        'api',
        'applicants',
    ];

    /**
     * FINAS Accounting (user_type = billing on the finas tenant). The FINAS
     * card was narrower than the default Accounting preset: only Applicants,
     * the Accounting (Statistics) dashboard and the Accounts module.
     *
     * (Card 6af3ddf8, 2026-11-11) "Show the AGENT REPORT, RECEIVABLE and
     * EXPENSE AND PAYMENT MODULEs to Admin, Accounting and Branch User
     * Access" on FINAS & CSDI — so those three are added here.
     */
    public const FINAS_ACCOUNTING_ALLOWED = [
        'dashboard',
        'notifications',
        'ai',
        'logout',
        'storage',
        'api',
        'applicants',
        'accounting',
        'accounts',
        'agent_report',
        'receivable',
        'expense_request',
    ];

    /** Modules "Rest of Account" may access (everything else is 403). */
    public const STAFF_ALLOWED = [
        'dashboard',
        'notifications',
        'ai',
        'company-profile',
        'logout',
        'storage',
        'applicants',
        'employers',
        'reports',
        'receivable',
        'expense_request',
        'api',
    ];

    /**
     * Agencies where the Agents Report module is offered.
     *
     * Originally Gulf-only (Mjolnir card "GULF: Agent Reports module",
     * 2026-11-09). Widened 2026-11-11 by card 6af3ddf8 — "FINAS & CSDI: AGENT
     * REPORT, RECEIVABLE and EXPENSE AND PAYMENT MODULE" — which asks for the
     * module on FINAS and CSDI as well, for Admin, Accounting and Branch.
     *
     * This list is intentionally explicit: an agency that is not named here
     * still gets nothing, so opening the module elsewhere stays a deliberate
     * decision rather than a side effect of a role change.
     */
    public const AGENT_REPORT_AGENCIES = ['gulf', 'finas', 'csdi'];

    /**
     * Agencies where Receivable + Expenses & Payments are offered to Branch
     * users (card 6af3ddf8, 2026-11-11).
     *
     * Branch users are otherwise branch-scoped and see neither module; the
     * card asks for both on FINAS and CSDI. Gulf is deliberately NOT included:
     * its Branch users keep the narrower access they have today.
     */
    public const BRANCH_FINANCE_AGENCIES = ['finas', 'csdi'];

    /**
     * General Ledger is Accounting-only (Mjolnir card review, Kchanz Josh
     * 2026-11-05): "Only Admin and Accounting users has this module".
     * It is therefore NOT in STAFF_ALLOWED — super_admin/admin keep it via
     * full access and Accounting (billing) via ACCOUNTING_DENIED not listing it.
     */

    /**
     * Route-name prefix => module. Order matters: longer/more specific
     * prefixes must come before shorter ones that share a stem
     * (e.g. report-templates before reports, agencies before agents).
     */
    public const ROUTE_MODULES = [
        'users'                    => 'users',
        'custom-fields'            => 'custom-fields',
        'custom-field-definitions' => 'custom-fields',
        'settings'                 => 'settings',
        // Accounts is its own module: Accounting (billing) keeps it while
        // Settings stays admin-only (FINAS card "Access to ... Accounts module").
        'accounts'                 => 'accounts',
        'status-codes'             => 'status-codes',
        'languages'                => 'languages',
        'skills'                   => 'skills',
        'report-templates'         => 'report-templates',
        'reports'                  => 'reports',
        'transactions'             => 'transactions',
        'employers'                => 'employers',
        'applicants'               => 'applicants',
        'receivable'               => 'receivable',
        'expense_request'          => 'expense_request',
        'agent_report'             => 'agent_report',
        'general_ledger'           => 'general_ledger',
        'agency.dashboard'         => 'dashboard',
        'dashboard'                => 'dashboard',
        'notifications'            => 'notifications',
        'ai'                       => 'ai',
        'company-profile'          => 'company-profile',
        'agencies'                 => 'agencies',
        'agents'                   => 'agents',
        'branches'                 => 'branches',
        'countries'                => 'countries',
        'positions'                => 'positions',
        'bills'                    => 'bills',
        'payments'                 => 'payments',
        'official-receipts'        => 'official-receipts',
        'commissions'              => 'commissions',
        'marketing-agencies'       => 'marketing',
        'api'                      => 'api',
        'accounting'               => 'accounting',
        'logout'                   => 'logout',
        'storage'                  => 'storage',
    ];

    /** Resolve the module for a request from its route name (falls back to the URI segment). */
    public static function moduleFor(?string $routeName, string $path): string
    {
        $name = (string) $routeName;

        if ($name !== '') {
            foreach (self::ROUTE_MODULES as $prefix => $module) {
                if ($name === $prefix || str_starts_with($name, $prefix.'.')) {
                    return $module;
                }
            }
        }

        $segment = explode('/', trim($path, '/'))[0] ?? '';

        return $segment !== '' ? $segment : 'other';
    }

    /** Whether a user_type may access a module at all (optionally per agency). */
    public static function allows(?string $userType, string $module, ?int $agencyId = null): bool
    {
        $userType = (string) $userType;

        // Gulf-only modules (Cyd 2026-11-09): Agents Report exists for the Gulf
        // Gulf-only modules (Cyd 2026-09): Agents Report used to exist for the
        // Gulf tenant alone. Checked BEFORE the full-access shortcut so an
        // admin of another agency cannot reach it either. Now offered on the
        // agencies named in AGENT_REPORT_AGENCIES (card 6af3ddf8).
        if ($module === 'agent_report' && ! self::isAnyAgency($agencyId, self::AGENT_REPORT_AGENCIES)) {
            return false;
        }

        if (in_array($userType, ['super_admin', 'admin'], true)) {
            return true;
        }

        if ($userType === 'billing') {
            // FINAS tenant: narrowed Accounting role per the FINAS card.
            if (self::isFinasAgency($agencyId)) {
                return in_array($module, self::FINAS_ACCOUNTING_ALLOWED, true);
            }

            return ! in_array($module, self::ACCOUNTING_DENIED, true);
        }

        if ($userType === 'encoder') {
            return in_array($module, self::ENCODER_ALLOWED, true);
        }

        if ($userType === 'recruitment') {
            return in_array($module, self::RECRUITMENT_ALLOWED, true);
        }

        if ($userType === 'branch') {
            // (Card 6af3ddf8, 2026-11-11) FINAS and CSDI Branch users get the
            // Receivable and Expenses & Payments modules. Branch data stays
            // branch-scoped by the existing branchLocked() rules, so a branch
            // user still only sees their own branch's rows.
            if (in_array($module, ['receivable', 'expense_request'], true)
                && self::isAnyAgency($agencyId, self::BRANCH_FINANCE_AGENCIES)) {
                return true;
            }

            return in_array($module, self::BRANCH_ALLOWED, true);
        }

        if ($userType === 'welfare') {
            return in_array($module, self::WELFARE_ALLOWED, true);
        }

        if ($userType === 'employer') {
            // FRA portal accounts (Mjolnir card "FINAS: FRA Feature"). They live
            // in /fra and must never reach agency-panel modules; only app chrome
            // is allowed so logout/notification links keep working.
            return in_array($module, ['notifications', 'logout', 'storage'], true);
        }

        return in_array($module, self::STAFF_ALLOWED, true);
    }

    /** True when the given agency is the FINAS tenant (subdomain = finas). */
    public static function isFinasAgency(?int $agencyId): bool
    {
        return self::isAgency($agencyId, 'finas');
    }

    /** True when the given agency has the supplied subdomain. */
    public static function isAgency(?int $agencyId, string $subdomain): bool
    {
        if (! $agencyId) {
            return false;
        }

        return strtolower((string) \App\Models\Agency::whereKey($agencyId)->value('subdomain')) === strtolower($subdomain);
    }

    /** True when the given agency's subdomain is one of the supplied list. */
    public static function isAnyAgency(?int $agencyId, array $subdomains): bool
    {
        if (! $agencyId) {
            return false;
        }

        $sub = strtolower((string) \App\Models\Agency::whereKey($agencyId)->value('subdomain'));

        return $sub !== '' && in_array($sub, array_map('strtolower', $subdomains), true);
    }

    /** True for the limited "Rest of Account" tier (everyone below admin/accounting, except Encoder). */
    public static function isRestOfAccount(?string $userType): bool
    {
        $userType = (string) $userType;

        // Encoder is its own (narrower) tier — the FRA/Reports read-only
        // carve-outs do not apply to it (those modules are denied outright).
        // The FINAS roles (Recruitment / Branch / Welfare) also have their own
        // explicit allowlists; the generic Rest-of-Account FRA/Reports
        // read-only carve-outs must not override them.
        return ! in_array($userType, [
            'super_admin', 'admin', 'billing', 'encoder',
            'recruitment', 'branch', 'welfare', 'employer',
        ], true);
    }
}
