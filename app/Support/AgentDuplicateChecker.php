<?php

namespace App\Support;

use App\Models\Agent;
use Illuminate\Support\Collection;

/**
 * Detects likely-duplicate agents within the same agency.
 *
 * Cyd 2026-11-08 (LANDAS card 6af02dd56cff0b42b7c91ee3): the Add Agent form
 * must warn when the same agent is being entered again, or almost the same
 * agent (JUAN CRUZ vs JUAN CRUZ STAFF).
 *
 * Keys, strongest first:
 *   - email  — exact, case-insensitive
 *   - contact (phone) — digits-only, suffix-tolerant
 *   - name   — exact after normalising case/whitespace, or fuzzy:
 *              a leading/trailing extra token (STAFF, JR, II…), else an edit
 *              distance of <=2 (<=1 for short names).
 *
 * This never blocks creation; the controller asks the user to confirm.
 */
class AgentDuplicateChecker
{
    /** Lower-case and collapse whitespace. */
    public static function norm(?string $v): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $v)));
    }

    /** Digits only — used for phone comparison. */
    public static function digits(?string $v): string
    {
        return preg_replace('/\D+/', '', (string) $v);
    }

    /**
     * Is an existing agent's name the same or almost the same?
     *
     * JUAN CRUZ vs JUAN CRUZ STAFF -> yes (extra trailing token).
     * JUAN CRUZ vs JUAN CROZ       -> yes (one-edit typo).
     * JUAN CRUZ vs MARIA CRUZ      -> no.
     */
    public static function nameSimilar(?string $existing, string $name): bool
    {
        $a = self::norm($existing);
        $b = self::norm($name);
        if ($a === '' || $b === '') {
            return false;
        }
        if ($a === $b) {
            return true;
        }

        // Extra leading/trailing token(s): the shorter name must appear as a
        // whole-word run at the start or end of the longer one
        // ("juan cruz" inside "juan cruz staff").
        $aTokens = preg_split('/\s+/', $a) ?: [];
        $bTokens = preg_split('/\s+/', $b) ?: [];
        if (count($aTokens) !== count($bTokens)) {
            $shorter = count($aTokens) < count($bTokens) ? $aTokens : $bTokens;
            $longer  = count($aTokens) < count($bTokens) ? $bTokens : $aTokens;
            $core    = implode(' ', $shorter);
            $prefix  = implode(' ', array_slice($longer, 0, count($shorter)));
            $suffix  = implode(' ', array_slice($longer, -count($shorter)));
            // Require a real identity core so "juan" alone does not match
            // everyone called "juan ...".
            $coreIsReal = count($shorter) >= 2 || mb_strlen($core) >= 6;
            if ($coreIsReal && ($core === $prefix || $core === $suffix)) {
                return true;
            }
        }

        $threshold = min(mb_strlen($a), mb_strlen($b)) < 6 ? 1 : 2;

        return levenshtein($a, $b) <= $threshold;
    }

    /**
     * Find existing agents in the agency that match the given input.
     *
     * @return Collection<int, array{agent: Agent, reasons: array<int, string>}>
     */
    public static function find(?int $agencyId, array $data, ?int $excludeId = null): Collection
    {
        $email   = self::norm($data['email'] ?? '');
        $contact = self::digits($data['contact'] ?? '');
        $name    = self::norm($data['name'] ?? '');

        $phoneUsable = $contact !== '' && strlen($contact) >= 7;

        if ($email === '' && ! $phoneUsable && $name === '') {
            return collect();
        }

        $query = Agent::query()->with(['branch', 'creator']);

        if ($agencyId) {
            $query->where('agents.agency_id', $agencyId);
        }
        if ($excludeId) {
            $query->where('agents.id', '!=', $excludeId);
        }

        $query->where(function ($q) use ($email, $contact, $phoneUsable, $name) {
            if ($email !== '') {
                $q->orWhereRaw('LOWER(agents.email) = ?', [$email]);
            }
            if ($phoneUsable) {
                $q->orWhereRaw(
                    "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(agents.contact,'-',''),' ',''),'(',''),')',''),'+','') LIKE ?",
                    ['%'.$contact.'%']
                );
            }
            if ($name !== '') {
                $q->orWhereRaw('LOWER(agents.name) = ?', [$name]);
                // Cheap prefilter: an extra-token name or a typo almost always
                // shares the first word.
                $firstToken = trim((string) preg_replace('/\s.*$/', '', $name));
                if (mb_strlen($firstToken) >= 3) {
                    $q->orWhereRaw('LOWER(agents.name) LIKE ?', [$firstToken.'%']);
                }
            }
        });

        $candidates = $query->orderByDesc('agents.created_at')->limit(40)->get();

        return $candidates
            ->map(function (Agent $a) use ($email, $contact, $phoneUsable, $name) {
                $reasons = self::reasonsFor($a, compact('email', 'contact', 'phoneUsable', 'name'));

                return ['agent' => $a, 'reasons' => $reasons];
            })
            ->filter(fn ($m) => ! empty($m['reasons']))
            ->values();
    }

    /** Human-readable reasons why an existing agent looks like a duplicate. */
    public static function reasonsFor(Agent $a, array $ctx): array
    {
        $email       = $ctx['email'] ?? '';
        $contact     = $ctx['contact'] ?? '';
        $phoneUsable = $ctx['phoneUsable'] ?? ($contact !== '' && strlen($contact) >= 7);
        $name        = $ctx['name'] ?? '';

        $reasons = [];

        if ($email !== '' && self::norm($a->email) === $email) {
            $reasons[] = 'Email matches ('.$a->email.')';
        }

        if ($phoneUsable) {
            $aDigits = self::digits($a->contact);
            if ($aDigits !== '' && ($aDigits === $contact || str_ends_with($aDigits, $contact) || str_ends_with($contact, $aDigits))) {
                $reasons[] = 'Contact number matches ('.$a->contact.')';
            }
        }

        if ($name !== '') {
            $eName = self::norm($a->name);
            if ($eName === $name) {
                $reasons[] = 'Same name ('.$a->name.')';
            } elseif (self::nameSimilar($a->name, $name)) {
                $reasons[] = 'Similar name ('.mb_strtoupper($name).' vs '.mb_strtoupper($eName).')';
            }
        }

        return $reasons;
    }
}
