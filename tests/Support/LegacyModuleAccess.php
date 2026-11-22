<?php

namespace Tests\Support;

use App\Models\Agency;

/**
 * The pre-refactor App\Support\ModuleAccess (HEAD 20f4a401), copied verbatim
 * except for `class ModuleAccess` -> `class LegacyModuleAccess` and
 * `self::` -> `static::`. It exists only so
 * ModuleAccessEquivalenceTest can prove the recorded baseline JSON is really
 * this code's output.
 *
 * DO NOT EDIT. It is frozen: changing it to agree with the new table is
 * exactly the failure the equivalence test exists to catch.
 *
 * `static::` (not `self::`) because the test binds this class name to a facade
 * root so these calls reach the instance; PHP resolves `self::` inside a class
 * bound to a root both statically and on the root, which would run the live
 * code — `static::` goes through the facade's __callStatic.
 */
class LegacyModuleAccess
{
    public const ACCOUNTING_DENIED = [
        'users',
        'custom-fields',
        'settings',
        'status-codes',
        'languages',
        'skills',
        'report-templates',
        'api',
        'audit_trail',
        'controlled_documents',
        'risks',
        'complaints',
        'non_conformities',
        'internal_audits',
        'compliance_calendar',
        'qms',
    ];

    public const ENCODER_ALLOWED = [
        'dashboard', 'notifications', 'ai', 'logout', 'storage', 'applicants',
    ];

    public const RECRUITMENT_ALLOWED = [
        'dashboard', 'notifications', 'ai', 'logout', 'storage', 'api',
        'applicants', 'employers', 'branches', 'countries', 'positions',
        'status-codes', 'languages', 'skills',
    ];

    public const BRANCH_ALLOWED = [
        'dashboard', 'notifications', 'ai', 'logout', 'storage', 'api',
        'applicants', 'employers', 'agent_report',
    ];

    public const QMS_MANAGER_ALLOWED = [
        'dashboard', 'controlled_documents', 'audit_trail', 'risks', 'complaints',
        'non_conformities', 'internal_audits', 'compliance_calendar', 'qms',
        'notifications', 'logout', 'storage',
    ];

    public const INTERNAL_AUDITOR_ALLOWED = [
        'dashboard', 'audit_trail', 'controlled_documents', 'risks', 'complaints',
        'non_conformities', 'internal_audits', 'compliance_calendar', 'qms',
        'notifications', 'logout', 'storage',
    ];

    public const WELFARE_ALLOWED = [
        'dashboard', 'notifications', 'ai', 'logout', 'storage', 'api', 'applicants',
    ];

    public const FINAS_ACCOUNTING_ALLOWED = [
        'dashboard', 'notifications', 'ai', 'logout', 'storage', 'api', 'applicants',
        'accounting', 'accounts', 'agent_report', 'receivable', 'expense_request',
    ];

    public const DMW_USER_ALLOWED = [
        'dashboard', 'notifications', 'ai', 'logout', 'storage', 'api',
        'company-profile', 'employees', 'applicants', 'applicants.withdrawn',
        'welfare', 'employers', 'reports',
    ];

    public const DMW_USER_AGENCIES = ['finas', 'csdi'];

    public const STAFF_ALLOWED = [
        'dashboard', 'notifications', 'ai', 'company-profile', 'logout', 'storage',
        'applicants', 'employers', 'reports', 'receivable', 'expense_request', 'api',
    ];

    public const AGENT_REPORT_AGENCIES = ['gulf', 'finas', 'csdi'];

    public const BRANCH_FINANCE_AGENCIES = ['finas', 'csdi'];

    public const EMPLOYEE_AGENCIES = ['csdi', 'finas'];

    public const WELFARE_MODULE_AGENCIES = ['csdi', 'finas'];

    public const QMS_MODULES = [
        'audit_trail', 'controlled_documents', 'risks', 'complaints',
        'non_conformities', 'internal_audits', 'compliance_calendar', 'qms',
    ];

    public const QMS_DEFAULT_ENABLED_AGENCIES = ['finas'];

    public const QMS_SETTINGS_KEY = 'qms_enabled';

    public static function allows(?string $userType, string $module, ?int $agencyId = null): bool
    {
        $userType = (string) $userType;

        if ($module === 'welfare' && ! static::isAnyAgency($agencyId, static::WELFARE_MODULE_AGENCIES)) {
            return false;
        }

        if ($module === 'employees' && ! static::isAnyAgency($agencyId, static::EMPLOYEE_AGENCIES)) {
            return false;
        }

        if ($module === 'agent_report' && ! static::isAnyAgency($agencyId, static::AGENT_REPORT_AGENCIES)) {
            return false;
        }

        if (static::isQmsModule($module) && ! static::qmsEnabled($agencyId)) {
            return false;
        }

        if (in_array($userType, ['super_admin', 'admin'], true)) {
            return true;
        }

        if ($userType === 'billing') {
            if (static::isFinasAgency($agencyId)) {
                return in_array($module, static::FINAS_ACCOUNTING_ALLOWED, true);
            }

            return ! in_array($module, static::ACCOUNTING_DENIED, true);
        }

        if ($userType === 'encoder') {
            return in_array($module, static::ENCODER_ALLOWED, true);
        }

        if ($userType === 'dmw_user') {
            if (! static::isAnyAgency($agencyId, static::DMW_USER_AGENCIES)) {
                return false;
            }

            return in_array($module, static::DMW_USER_ALLOWED, true);
        }

        if ($userType === 'recruitment') {
            return in_array($module, static::RECRUITMENT_ALLOWED, true);
        }

        if ($userType === 'branch') {
            if (in_array($module, ['receivable', 'expense_request'], true)
                && static::isAnyAgency($agencyId, static::BRANCH_FINANCE_AGENCIES)) {
                return true;
            }

            return in_array($module, static::BRANCH_ALLOWED, true);
        }

        if ($userType === 'welfare') {
            return in_array($module, static::WELFARE_ALLOWED, true);
        }

        if ($userType === 'qms_manager') {
            return in_array($module, static::QMS_MANAGER_ALLOWED, true);
        }

        if ($userType === 'internal_auditor') {
            return in_array($module, static::INTERNAL_AUDITOR_ALLOWED, true);
        }

        if ($userType === 'employer') {
            return in_array($module, ['notifications', 'logout', 'storage'], true);
        }

        return in_array($module, static::STAFF_ALLOWED, true);
    }

    public static function qmsEnabled(?int $agencyId): bool
    {
        if (! $agencyId) {
            return false;
        }

        $record = Agency::whereKey($agencyId)->first(['id', 'settings']);

        if (! $record) {
            return false;
        }

        $settings = $record->settings;
        $settings = is_object($settings) ? (array) $settings : (array) ($settings ?? []);

        if (array_key_exists(static::QMS_SETTINGS_KEY, $settings)) {
            return (bool) $settings[static::QMS_SETTINGS_KEY];
        }

        return static::isAnyAgency($agencyId, static::QMS_DEFAULT_ENABLED_AGENCIES);
    }

    public static function isQmsModule(string $module): bool
    {
        return in_array($module, static::QMS_MODULES, true);
    }

    public static function isFinasAgency(?int $agencyId): bool
    {
        return static::isAgency($agencyId, 'finas');
    }

    public static function isAgency(?int $agencyId, string $subdomain): bool
    {
        if (! $agencyId) {
            return false;
        }

        return strtolower((string) Agency::whereKey($agencyId)->value('subdomain')) === strtolower($subdomain);
    }

    public static function isAnyAgency(?int $agencyId, array $subdomains): bool
    {
        if (! $agencyId) {
            return false;
        }

        $sub = strtolower((string) Agency::whereKey($agencyId)->value('subdomain'));

        return $sub !== '' && in_array($sub, array_map('strtolower', $subdomains), true);
    }
}
