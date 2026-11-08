<?php

namespace App\Support;

use App\Models\Applicant;
use Illuminate\Support\Collection;

/**
 * Detects likely-duplicate applicants within the same agency.
 *
 * Introduced 2026-09-26 (Cyd / client request): warn the user when the
 * applicant they are about to create already looks like an existing record.
 *
 * Keys, strongest first:
 *   - passport_no (applicant_passports) — exact, case-insensitive
 *   - email                            — exact, case-insensitive
 *   - contact (phone)                  — digits-only, suffix-tolerant
 *   - first + last name                — normalised; + birthdate when present
 *   - ALMOST the same name             — Cyd 2026-11-08: last name equal and
 *                                        first name close (MARY vs MARRY) or
 *                                        the same tokens plus middle-name
 *                                        noise (MARY CORTEZ vs MARRY ANN CORTEZ).
 *
 * This never blocks creation; the controller asks the user to confirm.
 */
class ApplicantDuplicateChecker
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
     * Is an existing applicant's name "almost the same" as the input?
     *
     * Rule (Cyd 2026-11-08): same last name (normalised) AND
     *   - first name within a small edit distance (<=2, <=1 for short names), or
     *   - same first name with extra/missing middle-name tokens.
     *
     * MARY vs MARRY -> yes. MARY vs MARIA -> no (too different). Middle names
     * are ignored, so MARY CORTEZ == MARRY ANN CORTEZ.
     */
    public static function nameSimilar(?string $aFirst, ?string $aLast, ?string $aMiddle, string $first, string $last): bool
    {
        if ($first === '' || $last === '') {
            return false;
        }

        $eLast = self::norm($aLast);
        if ($eLast === '') {
            return false;
        }

        // Last name: exact, or a shared compound surname tail ("DELA CRUZ"
        // vs "CRUZ"). A one-letter typo is allowed on longer surnames.
        $eLastTail = trim((string) preg_replace('/^.*\s/', '', $eLast));
        $iLastTail = trim((string) preg_replace('/^.*\s/', '', $last));
        $lastOk = $eLast === $last
            || ($eLastTail === $iLastTail && mb_strlen($iLastTail) >= 3);
        if (! $lastOk && mb_strlen($last) >= 6 && mb_strlen($eLast) >= 6 && levenshtein($eLast, $last) <= 1) {
            $lastOk = true;
        }
        if (! $lastOk) {
            return false;
        }

        $eFirst = self::norm($aFirst);
        if ($eFirst === '') {
            return false;
        }
        if ($eFirst === $first) {
            return true;
        }

        // First name close enough (MARY vs MARRY). MARIA is 2 edits from MARY,
        // so short names only tolerate 1 edit.
        $threshold = min(mb_strlen($first), mb_strlen($eFirst)) < 6 ? 1 : 2;
        if (levenshtein($eFirst, $first) <= $threshold) {
            return true;
        }

        // First name may be stored/typed with the middle name appended
        // ("MARY ANN" vs "MARY"). Allow that when one side's first token is
        // the other's whole value, or both sides' first tokens are close.
        $eFirstTokens = preg_split('/\s+/', $eFirst) ?: [];
        $iFirstTokens = preg_split('/\s+/', $first) ?: [];
        if (count($eFirstTokens) > 1 && $eFirstTokens[0] === $first) {
            return true;
        }
        if (count($iFirstTokens) > 1 && $iFirstTokens[0] === $eFirst) {
            return true;
        }
        if (count($eFirstTokens) > 1 && count($iFirstTokens) > 1) {
            $aHead = $eFirstTokens[0];
            $bHead = $iFirstTokens[0];
            $headThreshold = min(mb_strlen($aHead), mb_strlen($bHead)) < 6 ? 1 : 2;

            return levenshtein($aHead, $bHead) <= $headThreshold;
        }

        return false;
    }

    /**
     * Find existing applicants in the agency that match the given input.
     *
     * @return Collection<int, array{applicant: Applicant, reasons: array<int, string>}>
     */
    public static function find(?int $agencyId, array $data, ?int $excludeId = null): Collection
    {
        $email    = self::norm($data['email'] ?? '');
        $contact  = self::digits($data['contact'] ?? '');
        $first    = self::norm($data['first_name'] ?? '');
        $last     = self::norm($data['last_name'] ?? '');
        $birth    = trim((string) ($data['birthdate'] ?? ''));
        $passport = mb_strtoupper(trim((string) ($data['passport_no'] ?? '')));

        $phoneUsable = $contact !== '' && strlen($contact) >= 7;
        $nameUsable  = $first !== '' && $last !== '';

        if ($email === '' && ! $phoneUsable && ! $nameUsable && $passport === '') {
            return collect();
        }

        $query = Applicant::query()->with(['passport', 'branch', 'agent', 'position', 'country', 'statusCode']);

        if ($agencyId) {
            $query->where('applicants.agency_id', $agencyId);
        }
        if ($excludeId) {
            $query->where('applicants.id', '!=', $excludeId);
        }

        // Cheap SQL prefilter for the fuzzy-name branch: same last name, or a
        // surname sharing the final token ("CRUZ" vs "DELA CRUZ"), or a
        // surname within one character on longer names. Regexp is skipped in
        // favour of LIKE so this stays MySQL/SQLite portable.
        $lastLast = $nameUsable ? trim((string) preg_replace('/^.*\s/', '', $last)) : '';

        $query->where(function ($q) use ($email, $contact, $phoneUsable, $first, $last, $lastLast, $nameUsable, $passport) {
            if ($email !== '') {
                $q->orWhereRaw('LOWER(applicants.email) = ?', [$email]);
            }
            if ($phoneUsable) {
                $q->orWhereRaw(
                    "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(applicants.contact,'-',''),' ',''),'(',''),')',''),'+','') LIKE ?",
                    ['%'.$contact.'%']
                );
            }
            if ($nameUsable) {
                $q->orWhere(function ($qq) use ($first, $last) {
                    $qq->whereRaw('LOWER(applicants.first_name) = ?', [$first])
                       ->whereRaw('LOWER(applicants.last_name) = ?', [$last]);
                });
                // Fuzzy: surname must line up before we spend two names' worth
                // of PHP comparison on the row.
                $q->orWhere(function ($qq) use ($last, $lastLast, $first) {
                    $qq->whereRaw('LOWER(applicants.last_name) = ?', [$last]);
                    // Same first letter keeps the candidate set small without
                    // dropping MARY/MARRY style typos.
                    $qq->whereRaw('LOWER(SUBSTR(applicants.first_name,1,1)) = ?', [mb_substr($first, 0, 1)]);
                });
                if ($lastLast !== '' && mb_strlen($lastLast) >= 3) {
                    $q->orWhere(function ($qq) use ($lastLast) {
                        $qq->whereRaw("LOWER(applicants.last_name) LIKE ?", ['%'.$lastLast]);
                    });
                }
            }
            if ($passport !== '') {
                $q->orWhereHas('passport', function ($pq) use ($passport) {
                    $pq->whereRaw('UPPER(applicant_passports.passport_no) = ?', [$passport]);
                });
            }
        });

        $candidates = $query->orderByDesc('applicants.created_at')->limit(40)->get();

        return $candidates
            ->map(function (Applicant $a) use ($email, $contact, $phoneUsable, $first, $last, $nameUsable, $birth, $passport) {
                $reasons = self::reasonsFor($a, compact('email', 'contact', 'phoneUsable', 'first', 'last', 'nameUsable', 'birth', 'passport'));

                return ['applicant' => $a, 'reasons' => $reasons];
            })
            ->filter(fn ($m) => ! empty($m['reasons']))
            ->values();
    }

    /** Human-readable reasons why an existing applicant looks like a duplicate. */
    public static function reasonsFor(Applicant $a, array $ctx): array
    {
        $email       = $ctx['email'] ?? '';
        $contact     = $ctx['contact'] ?? '';
        $phoneUsable = $ctx['phoneUsable'] ?? ($contact !== '' && strlen($contact) >= 7);
        $first       = $ctx['first'] ?? '';
        $last        = $ctx['last'] ?? '';
        $nameUsable  = $ctx['nameUsable'] ?? ($first !== '' && $last !== '');
        $birth       = $ctx['birth'] ?? '';
        $passport    = $ctx['passport'] ?? '';

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

        if ($passport !== '') {
            $aPass = mb_strtoupper(trim((string) optional($a->passport)->passport_no));
            if ($aPass !== '' && $aPass === $passport) {
                $reasons[] = 'Passport number matches ('.$aPass.')';
            }
        }

        // Name: report the strongest single reason — never both exact and fuzzy.
        if ($nameUsable) {
            $sameFirst = self::norm($a->first_name) === $first;
            $sameLast  = self::norm($a->last_name) === $last;

            if ($sameFirst && $sameLast) {
                $sameBirth = $birth !== '' && $a->birthdate && $a->birthdate->format('Y-m-d') === $birth;
                $reasons[] = ($sameBirth ? 'Same name and birthdate' : 'Same name').' ('.trim($a->first_name.' '.$a->last_name).')';
            } elseif (self::nameSimilar($a->first_name, $a->last_name, $a->middle_name, $first, $last)) {
                $reasons[] = 'Similar name ('.mb_strtoupper($first).' vs '.mb_strtoupper(self::norm($a->first_name)).' '
                    .mb_strtoupper(self::norm($a->last_name)).')';
            }
        }

        return $reasons;
    }
}
