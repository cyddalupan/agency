<?php

namespace App\Support;

/**
 * Central access map for web (agency) users — the single entry point.
 *
 * Role-to-module gating now lives in a declarative table
 * (App\Support\ModuleGating\GatingTable, applied by ModuleGate); this class
 * keeps the public API, the route→module mapping and the agency feature flags.
 *
 * Three access tiers beyond the full-access roles (super_admin/admin):
 *
 *  - Accounting (`billing`): full admin access MINUS the table's denylist.
 *  - "Rest of Account" (every other user_type): only the staff allowlist.
 *  - Encoder (`encoder`): ONLY the Dashboard + Applicant module.
 *
 * Introduced 2026-09-24 per client request (Cyd). Kept here so the middleware,
 * the sidebar, and the User model all read from one source of truth.
 * Encoder tier added 2026-10-08 (Mjolnir card "User Access Level 'Encoder'").
 * Gating extracted to GatingTable 2026-11-22 (card 6b008b8f3843d89fea14fa60):
 * zero behavior change, proven by ModuleAccessEquivalenceTest.
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
        // (Card 6afe3debac50fd61b181944b) The case-management API had no role
        // gate at all: /api/cases and /api/cases/search returned every case in
        // the agency to ANY authenticated user, each row eager-loading the full
        // applicant - and Applicant declares no $hidden, so that payload
        // carried names, birthdate, contact, address, parent names and remarks.
        // Billing is the tier that reaches it by falling through to
        // "admin minus denylist", so denying `api` here closes index and search
        // together at the one choke point every route in the group passes
        // through.
        //
        // Scope of this line, measured, because it is narrower than it looks:
        // it closes the hole for BILLING only. Every other tier that reaches
        // the API (staff, processor, paralegal, operation, branch, welfare,
        // recruitment, via STAFF_ALLOWED / BRANCH_ALLOWED / WELFARE_ALLOWED /
        // RECRUITMENT_ALLOWED, and billing on the FINAS tenant via
        // FINAS_ACCOUNTING_ALLOWED) still gets 200 and the same PII. Those
        // allowlists are a separate decision and are deliberately untouched
        // here. Do not read this entry as "the API is now gated".
        //
        // Also measured: the bcrypt hash was NOT in those responses. The
        // applicants table holds 0 non-null passwords, so the column
        // serialises as null in every row today. The credential exposure was
        // latent - one applicant with a password set would have leaked it -
        // not live. The PII leak was live.
        'api',
        // QMS-09 (card 6afa0da6eb58094f7e27f315): the audit trail and document
        // control are governance records owned by admin/QMS roles (spec §19).
        // Accounting never asked for them: before this they were visible to
        // billing only because the module gate is "admin minus denylist".
        // Listing them here keeps the sidebar and the controller allowlists
        // in agreement.
        'audit_trail',
        'controlled_documents',
        // QMS-12: the risk register is its own module, same governance
        // reasoning as above.
        'risks',
        // QMS-14: complaints are the same governance family.
        'complaints',
        // QMS-16: non-conformities and CAPA, likewise.
        'non_conformities',
        // QMS-18: internal audits are the same governance family.
        'internal_audits',
        // QMS-19: the compliance calendar is the same governance family.
        'compliance_calendar',
        // QMS-20: the QMS dashboard summarises all of the above.
        'qms',
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
     * QMS-09 (card 6afa0da6eb58094f7e27f315): QMS Manager gets full QMS
     * modules (spec §19).
     */
    public const QMS_MANAGER_ALLOWED = [
        'dashboard',
        'controlled_documents',
        'audit_trail',
        'risks',
        'complaints',
        'non_conformities',
        // QMS-18: the audit programme screens.
        'internal_audits',
        // QMS-19: the deadline feed pulls from every QMS module, so whoever
        // sees those modules may see the calendar they feed.
        'compliance_calendar',
        // QMS-20: the dashboard, ditto.
        'qms',
        'notifications',
        'logout',
        'storage',
    ];

    /**
     * QMS-09: Internal Auditor is READ-ONLY on audits (spec §19) — it may read
     * the trail and the document register, and record findings, but it cannot
     * manage controlled documents or alter source evidence.
     */
    public const INTERNAL_AUDITOR_ALLOWED = [
        'dashboard',
        'audit_trail',
        'controlled_documents',
        // QMS-12: an internal auditor audits the risk register, so it needs to
        // read it. Creating and editing risks is gated in the controller, the
        // same way document control splits read from manage.
        'risks',
        // QMS-14: a complaint register is auditable, but the auditor does not
        // handle complaints. Read-only is enforced in the controller.
        'complaints',
        // QMS-16: same for non-conformities — the auditor reads the CAPA
        // record it will sample, and cannot sign off its own findings.
        'non_conformities',
        // QMS-18: the auditor reads and works its own audits. Recording
        // findings is the auditor's job, so this is one of the few QMS modules
        // where the read-only role still writes — the controller splits plan
        // from conduct accordingly.
        'internal_audits',
        // QMS-19: read-only by nature, which suits the auditor exactly.
        'compliance_calendar',
        // QMS-20: the dashboard is read-only too.
        'qms',
        'notifications',
        'logout',
        'storage',
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

    /**
     * DMW User access level (card 6af81c30, 2026-11-14).
     *
     * "These should be the only modules on their access": Dashboard, Company
     * Profile, Employee, Applicant, Backout/Cancelled/Repat, Welfare, FRA,
     * Report. Everything else — accounting, receipts, users, settings, agent
     * (marketing) report, general ledger — is 403 and hidden from the sidebar.
     *
     * Backout/Cancelled/Repat and Welfare live under the applicants module in
     * ModuleAccess, so `applicants` covers them; the explicit entries below
     * keep the list readable against the card.
     * FINAS and CSDI only.
     */
    public const DMW_USER_ALLOWED = [
        'dashboard',
        'notifications',
        'ai',
        'logout',
        'storage',
        'api',
        'company-profile',
        'employees',
        'applicants',
        'applicants.withdrawn',
        'welfare',
        'employers',   // FRA (Foreign Recruitment Agency) module
        'reports',
    ];

    /** Agencies where the DMW User access level is offered (card 6af81c30). */
    public const DMW_USER_AGENCIES = ['finas', 'csdi'];

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
     * Agencies that offer the EMPLOYEE module (card 6af6fe94, 2026-11-13).
     *
     * "This is only for CSDI and FINAS" — the card is explicit, so the module
     * is closed everywhere else, admins included, the same way agent_report is
     * gated by AGENT_REPORT_AGENCIES.
     */
    public const EMPLOYEE_AGENCIES = ['csdi', 'finas'];

    /**
     * Agencies that offer the WELFARE module (card 6af81f6a, 2026-11-14).
     * "FINAS and CSDI only" — closed everywhere else, admins included.
     */
    public const WELFARE_MODULE_AGENCIES = ['csdi', 'finas'];

    /**
     * The QMS family (card 6afb2e83, 2026-11-16).
     *
     * The whole quality-management suite — audit trail, controlled documents,
     * risks, complaints, non-conformities, internal audits, the compliance
     * calendar and the QMS dashboard — is one switchable feature rather than
     * eight independent modules. Turning QMS off closes every one of them,
     * admins included, and hides their sidebar links.
     */
    public const QMS_MODULES = [
        'audit_trail',
        'controlled_documents',
        'risks',
        'complaints',
        'non_conformities',
        'internal_audits',
        'compliance_calendar',
        'qms',
    ];

    /**
     * Agencies that have QMS switched on unless their settings say otherwise.
     *
     * The card: "turned on on Finas and off in gulf for now". A switch in
     * Agency Settings overrides this default either way, so a new tenant does
     * not need a code change to get QMS.
     */
    public const QMS_DEFAULT_ENABLED_AGENCIES = ['finas'];

    /** Key in agencies.settings that records the per-agency QMS switch. */
    public const QMS_SETTINGS_KEY = 'qms_enabled';

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
        // QMS-08: controlled documents is its own module.
        'controlled_documents'     => 'controlled_documents',
        // QMS-03/QMS-09: the audit trail is its own module. Without this entry
        // moduleFor() falls through to the URL segment "audit-trail" (hyphen),
        // which never matches an underscore allowlist entry.
        'audit_trail'              => 'audit_trail',
        // QMS-12: the risk register is its own module.
        'risks'                    => 'risks',
        // QMS-14: complaints is its own module.
        'complaints'               => 'complaints',
        // QMS-16: non-conformities is its own module. The key must be the
        // route-name prefix (underscores, "non_conformities.*"), because
        // moduleFor() matches the route name first. The URL segment
        // "non-conformities" is only the fallback when a route has no name.
        'non_conformities'         => 'non_conformities',
        // QMS-18: internal audits. Key is the route-name prefix
        // ("internal_audits.*"), same rule as above.
        'internal_audits'          => 'internal_audits',
        // QMS-19: the compliance calendar. The URL segment is hyphenated, so
        // the route-name prefix mapping is required for the gate to match.
        'compliance_calendar'      => 'compliance_calendar',
        // QMS-20: the dashboard. Route names are "qms.dashboard" and
        // "metrics.*"; moduleFor() matches the prefix, so both are mapped.
        'qms'                      => 'qms',
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
        // Employee Settings shares the Employee module's gate.
        'welfare'                  => 'welfare',
        'employee-settings'        => 'employees',
        'employees'                => 'employees',
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

    /**
     * Whether a user_type may access a module at all (optionally per agency).
     *
     * Signature and behavior are unchanged since 2026-09-24; the decision
     * itself is now read off App\Support\ModuleGating\GatingTable by
     * ModuleGate. Precedence, before any role rule:
     *   feature gates (welfare, employees, agent_report, the QMS family) close
     *   the module for every role unless the agency carries it; then the
     *   user_type's role rule applies; then the "Rest of Account" default.
     * See GatingTable for the stage-by-stage table.
     *
     * The deprecated `$userType` first argument stays nullable and is cast to
     * string, so a null user type keeps falling through to the default rule
     * exactly as before.
     */
    public static function allows(?string $userType, string $module, ?int $agencyId = null, ?int $userId = null): bool
    {
        return \App\Support\ModuleGating\ModuleGate::allows($userType, $module, $agencyId, $userId);
    }

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

    /** True when the agency offers the EMPLOYEE module (CSDI / FINAS only). */
    public static function employeeEnabled(?int $agencyId): bool
    {
        return self::isAnyAgency($agencyId, self::EMPLOYEE_AGENCIES);
    }

    /**
     * True when the agency offers the Agents Report module (Gulf / FINAS /
     * CSDI). Read-only helper for callers that need the *name* of the feature
     * without going through the role rules (the Agents Report screen labels
     * itself differently per agency — see AgentTerm).
     *
     * Card 6b00a6ec: the label is a per-agency display name, not a second
     * module. The literal list stays in AGENT_REPORT_AGENCIES; this method
     * only exposes it.
     */
    public static function agentReportEnabled(?int $agencyId): bool
    {
        return self::isAnyAgency($agencyId, self::AGENT_REPORT_AGENCIES);
    }

    /**
     * Gulf only (card 6b056b38). The Previous Payment tab and its bulk upload
     * exist on the Agents Report for Gulf alone — FINAS and CSDI never see it.
     */
    public static function gulfOnly(?int $agencyId): bool
    {
        return self::isAnyAgency($agencyId, ['gulf']);
    }

    /**
     * Whether this agency has the QMS feature switched on.
     *
     * The agency's own switch in Agency Settings wins; when it has never been
     * set, the default list decides. Reading only the column value keeps this
     * cheap enough for the sidebar, which asks once per QMS link.
     */
    public static function qmsEnabled(?int $agencyId): bool
    {
        if (! $agencyId) {
            return false;
        }

        $record = \App\Models\Agency::whereKey($agencyId)->first(['id', 'settings']);

        if (! $record) {
            return false;
        }

        $settings = $record->settings;
        $settings = is_object($settings) ? (array) $settings : (array) ($settings ?? []);

        if (array_key_exists(self::QMS_SETTINGS_KEY, $settings)) {
            return (bool) $settings[self::QMS_SETTINGS_KEY];
        }

        return self::isAnyAgency($agencyId, self::QMS_DEFAULT_ENABLED_AGENCIES);
    }

    /** Whether the module belongs to the switchable QMS feature. */
    public static function isQmsModule(string $module): bool
    {
        return in_array($module, self::QMS_MODULES, true);
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
            'recruitment', 'branch', 'welfare', 'employer', 'dmw_user',
            // QMS-09: the QMS roles have their own explicit allowlists, so the
            // generic Rest-of-Account carve-outs must not override them.
            'qms_manager', 'internal_auditor',
        ], true);
    }
}
