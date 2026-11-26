<?php

namespace App\Support\ModuleGating;

/**
 * Declarative module-gating table (card 6b008b8f3843d89fea14fa60).
 *
 * This file is DATA. The precedence that used to be a 90-line if-ladder in
 * ModuleAccess::allows() is now three ordered stages, each of which can be read
 * off a constant below:
 *
 *   Stage 1 — FEATURE_GATES (evaluated in listed order, before any role rule):
 *       module => [rule => 'deny-unless-agency', agencies => [...subdomains...]]
 *
 *   Stage 2 — ROLE_RULES (a map keyed by user_type):
 *       user_type => [rule => ..., ...]
 *
 *   Stage 3 — DEFAULT_RULE — every user type not listed in ROLE_RULES.
 *
 * A rule is one of:
 *   'full'        -> allowed
 *   'deny-list'   -> denied when the module is in DENIED (Accounting)
 *   'allow-list'  -> allowed when the module is in ALLOWED (explicit tiers)
 *   'none'        -> denied (no rule applies to this module)
 *
 * Optional, on any rule:
 *   'agencies'  -> the rule applies inside those subdomains, and
 *                  'otherwise' (a list name, or 'deny-list') applies outside.
 *                  No 'otherwise' means "denied outside them".
 *   'modules'   -> extra modules allowed alongside 'allowed', inside the
 *                  rule's agencies only (the Branch finance carve-out).
 *
 * Equivalent of ModuleAccess::allows() exactly; proven by
 * tests/Feature/ModuleAccess/ModuleAccessEquivalenceTest.php against a recorded
 * baseline of the pre-refactor implementation. Reproduce oddities, do not fix.
 */
final class GatingTable
{
    /**
     * Stage 1. Ordered. Each entry closes a module for EVERY user type, admins
     * included, unless the agency's subdomain is listed.
     *
     * The agency lists are duplicated from the ModuleAccess constants instead
     * of referencing them: ModuleAccess must keep those public constants (they
     * are the public API other code reads and the out-of-scope feature flags),
     * and this table has to be readable as a table. The equivalence test's
     * universe includes every subdomain in the fixture agencies, so a drift
     * between the two copies goes red, not silent.
     */
    public const FEATURE_GATES = [
        // Card 6af81f6a: the WELFARE module is offered on CSDI and FINAS only.
        'welfare' => [
            'rule'     => 'deny-unless-agency',
            'agencies' => ['csdi', 'finas'],
        ],

        // Card 6af6fe94: the EMPLOYEE module is offered on CSDI and FINAS only.
        'employees' => [
            'rule'     => 'deny-unless-agency',
            'agencies' => ['csdi', 'finas'],
        ],

        // Cyd 2026-09 / card 6af3ddf8: Agents Report, widened from Gulf-only to
        // Gulf + FINAS + CSDI. Name the agencies explicitly; an agency not
        // listed gets nothing.
        'agent_report' => [
            'rule'     => 'deny-unless-agency',
            'agencies' => ['gulf', 'finas', 'csdi'],
        ],

        // Card 6afb2e83: QMS is ONE switchable feature, not eight modules.
        'audit_trail' => [
            'rule'     => 'deny-unless-agency',
            'agencies' => 'qms-enabled',
        ],
        'controlled_documents' => [
            'rule'     => 'deny-unless-agency',
            'agencies' => 'qms-enabled',
        ],
        'risks' => [
            'rule'     => 'deny-unless-agency',
            'agencies' => 'qms-enabled',
        ],
        'complaints' => [
            'rule'     => 'deny-unless-agency',
            'agencies' => 'qms-enabled',
        ],
        'non_conformities' => [
            'rule'     => 'deny-unless-agency',
            'agencies' => 'qms-enabled',
        ],
        'internal_audits' => [
            'rule'     => 'deny-unless-agency',
            'agencies' => 'qms-enabled',
        ],
        'compliance_calendar' => [
            'rule'     => 'deny-unless-agency',
            'agencies' => 'qms-enabled',
        ],
        'qms' => [
            'rule'     => 'deny-unless-agency',
            'agencies' => 'qms-enabled',
        ],
    ];

    /**
     * Stage 2. Ordered; first entry whose user_type matches decides, so an
     * entry must never appear twice (asserted by the equivalence test).
     *
     * `agencies` (suffix 'agencies') narrows the rule to those subdomains
     * first, exactly as the has_any_agency checks in the old ladder did.
     * `modules` (suffix 'modules') is a rule-specific escape hatch:
     *   branch + agencies  -> these modules are allowed (receivable, expense_request)
     */
    public const ROLE_RULES = [
        // Full-access roles. Nothing in this stage can narrow them; see
        // Stage 1 for the module-level gates that run before this.
        'super_admin' => ['rule' => 'full'],
        'admin'       => ['rule' => 'full'],

        // Accounting. FINAS tenant: narrowed allow-list. Elsewhere: "admin
        // minus the denylist", i.e. the default rule with the DENIED list.
        'billing' => [
            'rule'     => 'allow-list',
            'agencies' => ['finas'],
            'allowed'  => 'finas_accounting',
            'otherwise' => 'deny-list',
        ],

        'encoder' => ['rule' => 'allow-list', 'allowed' => 'encoder'],

        // Card 6b06d82a (Josh, 2026-11-26): "Display to users with branch
        // Davao, Bacolod, Iloilo, Pangasinan, Cdo". Those five are exactly the
        // Gulf branch accounts, and every one of them is user_type=operation
        // (measured: ids 251-255). 'operation' had no entry here at all, so it
        // fell through to DEFAULT_RULE (the STAFF_ALLOWED list, which has no
        // agent_report) and the module was denied for the entire tier.
        //
        // The `branch` list is the right target rather than a new list: it is
        // the same access a branch account gets, it already contains
        // agent_report, and it deliberately omits settings/users/etc. Branch
        // scoping is untouched and still enforced separately - see
        // AgentReportController::ledgerBranchLocked() and
        // User::isBranchLocked(), both of which return true for these users,
        // so each sees only the agents registered under their own branch.
        // Widening this list widens which SCREENS are reachable, not which
        // ROWS are visible.
        //
        // The list is `operation`, NOT `branch`. First attempt pinned this to
        // the `branch` list and the equivalence test caught it: `branch` has
        // no receivable / expense_request / reports / company-profile, so
        // operation users lost 78 existing grants across 13 agencies while
        // gaining agent_report. `operation` previously fell through to
        // DEFAULT_RULE => STAFF_ALLOWED, so the correct fix is that same list
        // plus agent_report, reproduced verbatim in OPERATION_ALLOWED.
        'operation' => ['rule' => 'allow-list', 'allowed' => 'operation'],

        // Card 6af81c30: DMW User exists on FINAS and CSDI only.
        'dmw_user' => [
            'rule'     => 'allow-list',
            'agencies' => ['finas', 'csdi'],
            'allowed'  => 'dmw_user',
        ],

        'recruitment' => ['rule' => 'allow-list', 'allowed' => 'recruitment'],

        // Card 6af3ddf8: FINAS/CSDI Branch users additionally get Receivable and
        // Expenses & Payments. Branch data stays branch-scoped by
        // branchLocked(), so this only widens which screens are reachable.
        'branch' => [
            'rule'      => 'allow-list',
            'agencies'  => ['finas', 'csdi'],
            'modules'   => ['receivable', 'expense_request'],
            'allowed'   => 'branch',
            // Outside FINAS/CSDI the fallback is the same `branch` list; the
            // old ladder re-checked the identical list (`return in_array($module,
            // BRANCH_ALLOWED)`), so this reproduces it rather than inventing a
            // separate copy that could drift.
            'otherwise' => 'branch',
        ],

        'welfare' => ['rule' => 'allow-list', 'allowed' => 'welfare'],

        // QMS-09: explicit allowlists; neither may fall through to the default.
        'qms_manager'      => ['rule' => 'allow-list', 'allowed' => 'qms_manager'],
        'internal_auditor' => ['rule' => 'allow-list', 'allowed' => 'internal_auditor'],

        // FRA portal accounts: app chrome only, so logout/notification links work.
        'employer' => ['rule' => 'allow-list', 'allowed' => 'employer'],
    ];

    /**
     * Stage 3: everyone else ("Rest of Account") — the STAFF_ALLOWED list.
     * Also the fallback for `billing` outside FINAS (deny-list rule).
     */
    public const DEFAULT_RULE = ['rule' => 'allow-list', 'allowed' => 'staff'];

    /**
     * Module lists. Keys are the `'allowed' => ...` names above. Denylist
     * entries keep their comments verbatim, which is why DENIED is the only
     * list here written in strict-in_array form (see ModuleAccess::DENIED).
     */
    public const LISTS = [
        // Accounting (billing) outside FINAS: everything except these.
        'denied' => [
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
        ],

        // Encoder: Dashboard + Applicant module only; infra entries are app
        // chrome, not business modules.
        'encoder' => [
            'dashboard',
            'notifications',
            'ai',
            'logout',
            'storage',
            'applicants',
        ],

        // Recruitment: Applicants + FRA + Reference Data + Lookups.
        'recruitment' => [
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
        ],

        // Branch: same as Recruitment EXCEPT Reference Data and Lookups. The
        // FINAS/CSDI finance modules are a rule-level carve-out (see ROLE_RULES),
        // not entries here, because they apply inside those agencies only.
        'branch' => [
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
        ],

        // QMS Manager: full QMS modules (spec §19).
        'qms_manager' => [
            'dashboard',
            'controlled_documents',
            'audit_trail',
            'risks',
            'complaints',
            'non_conformities',
            'internal_audits',
            'compliance_calendar',
            'qms',
            'notifications',
            'logout',
            'storage',
        ],

        // Internal Auditor: READ-ONLY on audits; controller splits read/manage.
        'internal_auditor' => [
            'dashboard',
            'audit_trail',
            'controlled_documents',
            'risks',
            'complaints',
            'non_conformities',
            'internal_audits',
            'compliance_calendar',
            'qms',
            'notifications',
            'logout',
            'storage',
        ],

        // Welfare: Applicants only, with full Case & Welfare tabs.
        'welfare' => [
            'dashboard',
            'notifications',
            'ai',
            'logout',
            'storage',
            'api',
            'applicants',
        ],

        // FINAS Accounting: only Applicants, the Accounting (Statistics)
        // dashboard and the Accounts module, plus card 6af3ddf8's three.
        'finas_accounting' => [
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
        ],

        // Card 6af81c30: DMW User's module list.
        'dmw_user' => [
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
        ],

        // "Rest of Account": the default allowlist.
        'staff' => [
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
        ],

        // Card 6b06d82a: OPERATION = STAFF_ALLOWED plus agent_report, and
        // nothing else. Kept as an explicit copy rather than "staff + " so
        // that a future edit to STAFF_ALLOWED does not silently re-open or
        // re-close agent_report for this tier without someone noticing. The
        // equivalence test pins every cell of the matrix, so any drift between
        // this list and what operation actually had goes red, not silent.
        'operation' => [
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
            'agent_report',
        ],

        // FRA portal accounts: app chrome only.
        'employer' => ['notifications', 'logout', 'storage'],
    ];
}
