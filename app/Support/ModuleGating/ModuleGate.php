<?php

namespace App\Support\ModuleGating;

/**
 * The evaluation engine for GatingTable. Deliberately tiny: all of the
 * decisions live in the table, this only applies them in order.
 *
 * Order of evaluation (must match the pre-refactor ModuleAccess::allows()):
 *
 *   1. Stage 1 — GatingTable::FEATURE_GATES, in listed order. The first entry
 *      for the requested module decides whether the agency carries the
 *      feature; if it does not, the answer is false and no role rule runs.
 *   2. Stage 2 — GatingTable::ROLE_RULES, keyed by user_type; a user type not
 *      listed there (or listed with no `rule`) answers "not allowed".
 *   3. Stage 3 — GatingTable::DEFAULT_RULE ("Rest of Account").
 *
 * A rule with an `agencies` list is evaluated the way the old ladder was:
 *   - inside those agencies  -> the rule itself, plus its `modules` carve-out;
 *   - outside them           -> its `otherwise` list, or false when it has none
 *                              (the branch finance carve-out is inside-only).
 *
 * Every agency check here is the same query ModuleAccess::isAnyAgency() makes
 * (Agency::whereKey($id)->value('subdomain'), lowercased, never equal to an
 * empty subdomain), so the two stay interchangeable by construction.
 */
final class ModuleGate
{
    public static function allows(?string $userType, string $module, ?int $agencyId = null): bool
    {
        // Stage 1: feature gates run before any role rule, admins included.
        foreach (GatingTable::FEATURE_GATES as $gatedModule => $gate) {
            if ($gatedModule !== $module) {
                continue;
            }

            if (! self::featureCarried($gate['agencies'], $agencyId)) {
                return false;
            }

            break; // one entry per module; the test asserts uniqueness.
        }

        // Stage 2: the user type's own rule. Stage 3 otherwise.
        $userType = (string) $userType;
        $rule = GatingTable::ROLE_RULES[$userType] ?? GatingTable::DEFAULT_RULE;

        return self::apply($rule, $module, $agencyId);
    }

    /**
     * @param  list<string>|string  $agencies  subdomains, or 'qms-enabled'
     */
    private static function featureCarried(array|string $agencies, ?int $agencyId): bool
    {
        if ($agencies === 'qms-enabled') {
            // A switch, not a list: the agency's own settings win over the
            // QMS_DEFAULT_ENABLED_AGENCIES default (see ModuleAccess::qmsEnabled).
            return \App\Support\ModuleAccess::qmsEnabled($agencyId);
        }

        return self::inAgencies($agencyId, $agencies);
    }

    /**
     * @param  array{rule?: string, allowed?: string, modules?: list<string>, agencies?: list<string>, otherwise?: string}  $rule
     */
    private static function apply(array $rule, string $module, ?int $agencyId): bool
    {
        $ruleName = $rule['rule'] ?? 'none';

        if (isset($rule['agencies'])) {
            $inAgency = self::inAgencies($agencyId, $rule['agencies']);

            if ($inAgency === false) {
                return self::outsideAgencies($rule, $rule['otherwise'] ?? null, $module);
            }
        }

        return self::insideRule($rule, $ruleName, $module);
    }

    /**
     * The agency check failed: apply the rule's fallback list (`otherwise`),
     * or part of the old "admin minus denylist" shape, or deny.
     */
    private static function outsideAgencies(array $rule, ?string $otherwise, string $module): bool
    {
        if ($otherwise === 'deny-list') {
            return ! in_array($module, GatingTable::LISTS['denied'], true);
        }

        if ($otherwise === null) {
            return false;
        }

        return in_array($module, GatingTable::LISTS[$otherwise], true);
    }

    /** The rule itself, with the optional `modules` carve-out. */
    private static function insideRule(array $rule, string $ruleName, string $module): bool
    {
        if ($ruleName === 'full') {
            return true;
        }

        if ($ruleName === 'deny-list') {
            return ! in_array($module, GatingTable::LISTS['denied'], true);
        }

        if ($ruleName === 'allow-list') {
            if (in_array($module, GatingTable::LISTS[$rule['allowed']], true)) {
                return true;
            }

            return isset($rule['modules']) && in_array($module, $rule['modules'], true);
        }

        return false;
    }

    /** @param  list<string>  $subdomains */
    private static function inAgencies(?int $agencyId, array $subdomains): bool
    {
        $sub = self::subdomain($agencyId);

        return $sub !== '' && in_array($sub, array_map('strtolower', $subdomains), true);
    }

    private static function subdomain(?int $agencyId): string
    {
        if (! $agencyId) {
            return '';
        }

        // Deliberately uncached: a static memo survives the request and leaks
        // one agency's subdomain into the next test/tenant in the same process
        // (tests: EmployeeModuleTest "other tenants...", WelfareModuleTest
        // "records never leak between agencies"). The old implementation
        // queried every call; this keeps the same read-per-call cost.
        return strtolower(
            (string) \App\Models\Agency::whereKey($agencyId)->value('subdomain')
        );
    }
}
