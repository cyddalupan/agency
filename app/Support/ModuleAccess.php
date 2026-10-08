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
        'agent_report',
        'api',
    ];

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
        'accounts'                 => 'settings',
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

    /** Whether a user_type may access a module at all. */
    public static function allows(?string $userType, string $module): bool
    {
        $userType = (string) $userType;

        if (in_array($userType, ['super_admin', 'admin'], true)) {
            return true;
        }

        if ($userType === 'billing') {
            return ! in_array($module, self::ACCOUNTING_DENIED, true);
        }

        if ($userType === 'encoder') {
            return in_array($module, self::ENCODER_ALLOWED, true);
        }

        return in_array($module, self::STAFF_ALLOWED, true);
    }

    /** True for the limited "Rest of Account" tier (everyone below admin/accounting, except Encoder). */
    public static function isRestOfAccount(?string $userType): bool
    {
        $userType = (string) $userType;

        // Encoder is its own (narrower) tier — the FRA/Reports read-only
        // carve-outs do not apply to it (those modules are denied outright).
        return ! in_array($userType, ['super_admin', 'admin', 'billing', 'encoder'], true);
    }
}
