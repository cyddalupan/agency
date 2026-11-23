<?php

namespace App\Support;

/**
 * Card 6af81c30 (part 1, Josh 2026-11-14 "B. All Agent/s word will be renamed
 * to Marketing Staff — CSDI, FINAS only").
 *
 * FINAS and CSDI call their agents "Marketing Staff". Every other tenant keeps
 * "Agent". The two vocabularies name the same underlying record (agents table,
 * agent_id FK), so this only ever changes what is *printed*, never schema,
 * routes or permissions — a rename must not fork the data model.
 */
class AgentTerm
{
    /** Agencies that use "Marketing Staff" instead of "Agent". */
    public const RENAME_AGENCIES = ['finas', 'csdi'];

    /** The noun used for one agent in this agency. */
    public static function noun(?int $agencyId = null): string
    {
        return self::usesMarketingStaff($agencyId) ? 'Marketing Staff' : 'Agent';
    }

    /** Plural form of {@see noun()}. */
    public static function plural(?int $agencyId = null): string
    {
        return self::usesMarketingStaff($agencyId) ? 'Marketing Staff' : 'Agents';
    }

    /** True when the current agency prints "Marketing Staff". */
    public static function active(): bool
    {
        return self::usesMarketingStaff(null);
    }

    /**
     * The module's display name in this agency.
     *
     * Card 6b00a6ec (Cyd): Gulf shows "Agents Report", CSDI and FINAS show
     * "Marketing Staff Report".  This is a LABEL, not a second module: the
     * route, the route name (`agent_report.*`), the gate (`agent_report`), the
     * tables and the permissions are one set for all three agencies.  Two
     * modules would mean two gates to keep in sync and two sidebar branches
     * for one screen; a label costs one call site.
     *
     * The vocabulary comes from the same per-agency rename that already powers
     * agent_terms() (card 6af81c30, RENAME_AGENCIES), and whether the screen
     * exists at all comes from ModuleAccess::AGENT_REPORT_AGENCIES — so an
     * agency moved in or out of the allow-list changes its name automatically,
     * with no second list to edit.
     */
    public static function reportModuleName(?int $agencyId = null): string
    {
        return self::plural($agencyId) . ' Report';
    }

    /** True when the agency offers the Agents Report module (Gulf / CSDI / FINAS). */
    public static function reportModuleEnabled(?int $agencyId = null): bool
    {
        if ($agencyId === null) {
            $agencyId = resolve_agency()?->id;
        }

        return ModuleAccess::agentReportEnabled($agencyId);
    }

    /**
     * Replace "Agent"/"Agents" inside an existing UI string, e.g. a page title
     * that already reads "Agents Report". Keeps the original casing shape.
     */
    public static function text(string $value, ?int $agencyId = null): string
    {
        if (! self::usesMarketingStaff($agencyId)) {
            return $value;
        }

        // Plural first so "Agents" is not half-replaced into "Marketing Staffs".
        return str_ireplace(
            ['agents', 'agent'],
            ['Marketing Staff', 'Marketing Staff'],
            $value
        );
    }

    private static function usesMarketingStaff(?int $agencyId): bool
    {
        if ($agencyId === null) {
            $agency = resolve_agency();
            $agencyId = $agency?->id;
        }

        return ModuleAccess::isAnyAgency($agencyId, self::RENAME_AGENCIES);
    }
}
