<?php

declare(strict_types=1);

namespace App\Services\GodMode;

use App\Models\Agency;
use App\Models\User;
use App\Support\ModuleAccess;
use App\Support\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * Card 6b03439e6043673862503f2b — the operator-facing per-agency settings.
 *
 * `agencies.settings` is the store. This class owns the one definition of
 * "the document", "the hash", "the defaults", "what merge means" and "what
 * changed", so the controller never inspects JSON and the form, the write and
 * the audit row cannot disagree about what a key holds.
 *
 * Load-bearing rules (each one silently corrupts data when undone):
 *
 *   1. Eloquent returns the `json` cast as a stdClass (or null). A writer that
 *      assumes an array blanks the column. `document()` normalises; every
 *      other method goes through it.
 *
 *   2. Merge, never replace. Unknown keys are preserved: `primary_color`,
 *      `applicant_qr_token`, brand fields and anything a future reader adds
 *      survive an operator save untouched.
 *
 *   3. Only `qms_enabled` can be DELETED. Its "Default" state is the absent
 *      key, because finas resolves on with no key stored and writing `false`
 *      would switch QMS off.
 *
 *   4. A save that changes nothing writes nothing — no update, no audit, no
 *      activity. The document stays byte-identical.
 *
 *   5. Merge + write + audit in one transaction. A throw changes nothing.
 *
 *   6. Concurrent edits lose, they do not overwrite: the form carries the md5
 *      of the document it rendered and a changed hash refuses the save (422).
 *      Last-write-wins is unacceptable for a feature switch.
 */
class GodAgencySettingsService
{
    public const AUDIT_ACTION_UPDATED = 'godmode.agency_settings_updated';

    public const BYPASS_REASON = 'godmode: update per-agency settings';

    /** The one key this screen may delete. */
    public const DELETABLE = 'qms_enabled';

    /** QMS tri-state values. */
    public const QMS_ON = 'on';
    public const QMS_OFF = 'off';
    public const QMS_DEFAULT = 'default';

    /** Defaults for the complaint SLA, mirroring `Complaint::slaFor()`. */
    public const SLA_ACK_DEFAULT = 2;
    public const SLA_RESOLVE_DEFAULT = 15;

    // ── document ─────────────────────────────────────────────────────

    /**
     * The stored document as a plain array. NULL becomes empty, a stdClass is
     * converted, a JSON string is decoded, anything else is coerced.
     */
    public function document(?Agency $agency): array
    {
        if (! $agency) {
            return [];
        }

        return self::normalise($agency->settings);
    }

    /** stdClass|array|null|string -> array. The one normaliser. */
    public static function normalise($raw): array
    {
        if ($raw === null) {
            return [];
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }

        if (is_object($raw)) {
            $raw = json_decode((string) json_encode($raw), true);
        }

        return is_array($raw) ? $raw : [];
    }

    /**
     * Hash of the document. The form carries it; a mismatch means someone
     * else saved while the operator had the page open.
     */
    public function hash(array $document): string
    {
        return md5($this->canonical($document));
    }

    public function hashOf(?Agency $agency): string
    {
        return $this->hash($this->document($agency));
    }

    /** Deterministic JSON so a key-order change is not mistaken for an edit. */
    private function canonical(array $document): string
    {
        return (string) json_encode(
            $this->ksortRecursive($document),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    private function ksortRecursive(array $data): array
    {
        ksort($data);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->ksortRecursive($value);
            }
        }

        return $data;
    }

    /** The canonical form, exposed for the tests that prove hash stability. */
    public function canonicalForm(array $document): string
    {
        return $this->canonical($document);
    }

    // ── defaults: the reader table, one place ────────────────────────

    /**
     * Every key this screen knows about, its default when absent, and who
     * reads it. Read-only entries carry no editable default.
     *
     * `default` is what the READER falls back to. Labels are literal English,
     * like the rest of the god plane.
     */
    public function keys(Agency $agency): array
    {
        return [
            'qms_enabled' => [
                'label'  => 'Quality Management (QMS)',
                'reader' => 'ModuleAccess::qmsEnabled()',
                'default' => $this->qmsDefault((string) $agency->subdomain),
                'default_note' => 'On for finas only (code list, not a stored key).',
            ],
            'applicants.hide_type_tab_counts' => [
                'label'  => 'Hide applicant-type tab counters',
                'reader' => 'hide_type_tab_count()',
                'default' => false,
                'default_note' => 'Hidden when the agency name contains GULF (fallback kept).',
            ],
            'complaint_sla.ack_days' => [
                'label'  => 'Acknowledgement window (days)',
                'reader' => 'Complaint::slaFor()',
                'default' => self::SLA_ACK_DEFAULT,
            ],
            'complaint_sla.resolve_days' => [
                'label'  => 'Resolution window (days)',
                'reader' => 'Complaint::slaFor()',
                'default' => self::SLA_RESOLVE_DEFAULT,
            ],
            'applicant_form_defaults.enable_firstimer' => [
                'label'  => 'Enable first-timer option',
                'reader' => 'app_applicant_form_defaults()',
                'default' => false,
            ],
            'color_theme' => [
                'label'  => 'Colour theme',
                'reader' => 'app_color_theme()',
                'default' => 'default',
            ],
            'all_caps' => [
                'label'  => 'All caps site text',
                'reader' => 'app_site_all_caps()',
                'default' => false,
            ],
            'banner_color' => [
                'label'  => 'Banner colour',
                'reader' => 'app_site_banner_color()',
                'default' => null,
                'default_note' => 'Gold when unset.',
            ],
            'hide_company_profile' => [
                'label'  => 'Show the Company Profile page',
                'reader' => 'app_show_company_profile()',
                // The stored key is the inverse of the label.
                'default' => true,
                'default_note' => 'Shown when unset (stored key is inverted).',
            ],
            // Read-only below this line.
            'applicant_qr_token' => ['label' => 'Applicant QR token', 'reader' => 'ApplicantQrController', 'default' => null, 'read_only' => true],
            'applicants_table_columns' => ['label' => 'Applicants table columns', 'reader' => 'app_applicant_table_columns()', 'default' => null, 'read_only' => true],
            'primary_color' => ['label' => 'Primary colour', 'reader' => 'branding', 'default' => null, 'read_only' => true],
            'secondary_color' => ['label' => 'Secondary colour', 'reader' => 'branding', 'default' => null, 'read_only' => true],
            'favicon' => ['label' => 'Favicon', 'reader' => 'branding', 'default' => null, 'read_only' => true],
        ];
    }

    /** QMS default, from the code list — never from a stored key. */
    public function qmsDefault(string $subdomain): bool
    {
        $subdomain = strtolower(trim($subdomain));

        return $subdomain !== ''
            && in_array($subdomain, array_map('strtolower', ModuleAccess::QMS_DEFAULT_ENABLED_AGENCIES), true);
    }

    /** The keys the form owns; everything else is preserved and read-only. */
    public function editableKeys(Agency $agency): array
    {
        return array_keys(array_filter($this->keys($agency), fn (array $meta) => empty($meta['read_only'])));
    }

    public function readOnlyKeys(Agency $agency): array
    {
        return array_keys(array_filter($this->keys($agency), fn (array $meta) => ! empty($meta['read_only'])));
    }

    // ── flatten / diff ───────────────────────────────────────────────

    /** Dotted-path view: `complaint_sla.ack_days => 2`. Lists stay leaves. */
    public function flatten(array $document, string $prefix = ''): array
    {
        $flat = [];

        foreach ($document as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value) && $value !== [] && ! array_is_list($value)) {
                $flat += $this->flatten($value, $path);

                continue;
            }

            $flat[$path] = $value;
        }

        return $flat;
    }

    /**
     * Dotted-path differences, changed keys only, values compared strictly
     * (`false` is not `0`, `null` is not absent). Keys present on only one
     * side count as changed.
     */
    public function diff(array $before, array $after): array
    {
        $a = $this->flatten($before);
        $b = $this->flatten($after);

        $changed = [];

        foreach (array_unique(array_merge(array_keys($a), array_keys($b))) as $path) {
            $had = array_key_exists($path, $a);
            $has = array_key_exists($path, $b);

            if ($had !== $has || $a[$path] !== $b[$path]) {
                $changed[$path] = true;
            }
        }

        return $changed;
    }

    // ── merge ────────────────────────────────────────────────────────

    /**
     * Apply the form's choices onto the stored document.
     *
     * `$input` is validated request data. Absent input leaves the stored value
     * alone. `qms_enabled = default` DELETES the key; it is the only deletable
     * key. An empty `banner_color` unsets the key (empty = unset, per spec).
     *
     * The form owns every editable key, so the booleans arrive as explicit
     * `false` when a box is unchecked. `false` is also the reader's fallback
     * for all of them, so an explicit `false` with nothing stored must not
     * create `key: false`: that would make a no-op submit write a row (§4.2
     * forbids it). Presence follows the value — `false` where `false` is the
     * default deletes the key. Same for the SLA: a submitted value equal to
     * the effective (stored or default) value is left exactly as it was, so
     * "Default" stays absent instead of being frozen into the document.
     */
    public function merge(array $document, array $input): array
    {
        $merged = $document;

        // Only the match arm for `default` deletes. Anything that is not a
        // recognised tri-state value throws: a silent fallthrough to delete
        // would turn a typo into a feature being switched back to default.
        if (array_key_exists('qms_enabled', $input)) {
            $qms = (string) $input['qms_enabled'];

            if (! in_array($qms, [self::QMS_ON, self::QMS_OFF, self::QMS_DEFAULT], true)) {
                throw new \InvalidArgumentException('Unknown QMS state: '.$qms);
            }

            $merged = match ($qms) {
                self::QMS_ON  => $this->setPath($merged, 'qms_enabled', true),
                self::QMS_OFF => $this->setPath($merged, 'qms_enabled', false),
                self::QMS_DEFAULT => $this->forgetPath($merged, 'qms_enabled'),
            };
        }

        $merged = $this->putBoolean($merged, 'applicants.hide_type_tab_counts', $input, 'hide_type_tab_counts', false);
        $merged = $this->putBoolean($merged, 'applicant_form_defaults.enable_firstimer', $input, 'enable_firstimer', false);
        $merged = $this->putBoolean($merged, 'all_caps', $input, 'all_caps', false);

        if (isset($input['complaint_sla']) && is_array($input['complaint_sla'])) {
            $effective = $this->slaValues($merged);

            foreach (['ack_days', 'resolve_days'] as $part) {
                if (! array_key_exists($part, $input['complaint_sla'])) {
                    continue;
                }

                // Submitting the effective value is not an edit: keep the
                // key's presence exactly as it was.
                if ((int) $input['complaint_sla'][$part] === $effective[$part]) {
                    continue;
                }

                $merged = $this->setPath($merged, 'complaint_sla.'.$part, (int) $input['complaint_sla'][$part]);
            }
        }

        if (array_key_exists('color_theme', $input)) {
            $colour = (string) $input['color_theme'];

            // Submitting the effective theme is not an edit. Storing it would
            // turn "follow the default" into a frozen value for a key nobody
            // touched (§4.2: absent controls keep the truth, no-op writes nothing).
            $effective = (string) ($merged['color_theme'] ?? 'default');
            $effective = array_key_exists($effective, app_color_themes()) ? $effective : 'default';

            if ($colour !== $effective) {
                $merged = $this->setPath($merged, 'color_theme', $colour);
            }
        }

        if (array_key_exists('banner_color', $input)) {
            $colour = trim((string) ($input['banner_color'] ?? ''));
            $merged = $colour === ''
                ? $this->forgetPath($merged, 'banner_color')
                : $this->setPath($merged, 'banner_color', $colour);
        }

        // The key name is a trap: stored `hide_company_profile`, labelled
        // "Show the Company Profile page". The form posts the LABEL's meaning,
        // so it is inverted here before the presence rule applies.
        if (array_key_exists('show_company_profile', $input)) {
            $merged = $this->putBoolean(
                $merged,
                'hide_company_profile',
                ['hide_company_profile' => ! (bool) $input['show_company_profile']],
                'hide_company_profile',
                false
            );
        }

        return $merged;
    }

    /**
     * Set a boolean dotted path only when it differs from the effective
     * (stored or default) value. Where `false` is the default, `false` deletes
     * the key instead of storing it, so the document says "unset" honestly.
     */
    private function putBoolean(array $document, string $path, array $input, string $inputKey, bool $default): array
    {
        if (! array_key_exists($inputKey, $input)) {
            return $document;
        }

        $value = (bool) $input[$inputKey];
        $stored = $this->path($document, $path);
        $stored = $stored === null ? $default : (bool) $stored;

        if ($value === $stored) {
            return $document;
        }

        return $value === $default
            ? $this->forgetPath($document, $path)
            : $this->setPath($document, $path, $value);
    }

    /** Read a dotted path; null when any parent is missing. */
    private function path(array $document, string $path)
    {
        $ref = $document;

        foreach (explode('.', $path) as $segment) {
            if (! is_array($ref) || ! array_key_exists($segment, $ref)) {
                return null;
            }
            $ref = $ref[$segment];
        }

        return $ref;
    }

    private function setPath(array $document, string $path, $value): array
    {
        $ref = &$document;

        foreach (explode('.', $path) as $segment) {
            if (! isset($ref[$segment]) || ! is_array($ref[$segment])) {
                $ref[$segment] = [];
            }
            $ref = &$ref[$segment];
        }

        $ref = $value;
        unset($ref);

        return $document;
    }

    /** Delete a dotted path and prune the parents it empties. */
    private function forgetPath(array $document, string $path): array
    {
        $segments = explode('.', $path);
        $leaf = array_pop($segments);

        if ($segments === []) {
            unset($document[$leaf]);

            return $document;
        }

        $refs = [];
        $ref = &$document;

        foreach ($segments as $segment) {
            if (! isset($ref[$segment]) || ! is_array($ref[$segment])) {
                return $document;
            }
            $refs[] = &$ref;
            $ref = &$ref[$segment];
        }

        unset($ref[$leaf]);
        unset($ref);

        // Prune now-empty parents so `{}` does not linger as a phantom key.
        for ($i = count($segments) - 1; $i >= 0; $i--) {
            $node = &$refs[$i];
            $key = $segments[$i];

            if (isset($node[$key]) && is_array($node[$key]) && $node[$key] === []) {
                unset($node[$key]);
            }
            unset($node);
        }

        return $document;
    }

    // ── read model for the form / read-only panel ────────────────────

    /**
     * The form's current values, each with its source: stored, default or
     * absent. Nothing is invented; the view renders exactly this.
     */
    public function formState(Agency $agency): array
    {
        $document = $this->document($agency);
        $storedSla = $document['complaint_sla'] ?? [];
        $storedSla = is_array($storedSla) ? $storedSla : [];
        $sla = $this->slaValues($document);
        $applicants = (array) ($document['applicants'] ?? []);

        return [
            'qms_enabled'          => array_key_exists('qms_enabled', $document)
                ? ((bool) $document['qms_enabled'] ? self::QMS_ON : self::QMS_OFF)
                : self::QMS_DEFAULT,
            'qms_stored'           => array_key_exists('qms_enabled', $document),
            'qms_default_on'       => $this->qmsDefault((string) $agency->subdomain),
            'hide_type_tab_counts' => array_key_exists('hide_type_tab_counts', $applicants)
                ? (bool) $applicants['hide_type_tab_counts']
                // The GULF name fallback is real and kept (§11): an agency
                // with nothing stored still hides the counters, so the box
                // must show what the reader actually does.
                : (bool) preg_match('/GULF/i', (string) $agency->name),
            'hide_type_tab_counts_stored' => array_key_exists('hide_type_tab_counts', $applicants),
            'ack_days'             => $sla['ack_days'],
            'resolve_days'         => $sla['resolve_days'],
            'ack_days_stored'      => array_key_exists('ack_days', $storedSla),
            'resolve_days_stored'  => array_key_exists('resolve_days', $storedSla),
            'effective_ack_days'   => $sla['ack_days'],
            'enable_firstimer'     => (bool) ($document['applicant_form_defaults']['enable_firstimer'] ?? false),
            'color_theme'          => app_color_theme($agency),
            'all_caps'             => (bool) ($document['all_caps'] ?? false),
            'banner_color'         => (string) ($document['banner_color'] ?? ''),
            'show_company_profile' => empty($document['hide_company_profile']),
            'settings_version'     => $this->hash($document),
            'keys'                 => $this->keys($agency),
        ];
    }

    /**
     * Effective SLA windows for an agency: stored value when valid, otherwise
     * the reader's default. The form is validated against THIS, not against
     * the defaults, so a form that moved only `resolve_days` cannot slip under
     * a stored `ack_days` it never rendered as the default.
     */
    public function sla(Agency $agency): array
    {
        $sla = $this->document($agency)['complaint_sla'] ?? [];
        $sla = is_array($sla) ? $sla : [];

        return $this->slaValues($sla);
    }

    /** Effective ack/resolve from a `complaint_sla` fragment (or a whole document). */
    private function slaValues(array $documentOrFragment): array
    {
        $sla = $documentOrFragment['complaint_sla'] ?? $documentOrFragment;
        $sla = is_array($sla) ? $sla : [];

        return [
            'ack_days'     => $this->intOrDefault($sla['ack_days'] ?? null, self::SLA_ACK_DEFAULT),
            'resolve_days' => $this->intOrDefault($sla['resolve_days'] ?? null, self::SLA_RESOLVE_DEFAULT),
        ];
    }

    private function intOrDefault($value, int $default): int
    {
        if (! is_numeric($value)) {
            return $default;
        }

        $days = (int) $value;

        return $days >= 1 ? $days : $default;
    }

    /**
     * Everything the form does not own, dotted and humanised, for the
     * read-only panel. Unknown keys appear as-is (criterion 14).
     */
    public function readOnlyValues(Agency $agency): array
    {
        $editable = $this->editableKeys($agency);
        $rows = [];

        foreach ($this->flatten($this->document($agency)) as $path => $value) {
            if (in_array($path, $editable, true)) {
                continue;
            }

            $rows[$path] = $value;
        }

        ksort($rows);

        return $rows;
    }

    public function maskedQrToken(Agency $agency): ?string
    {
        $token = $this->document($agency)['applicant_qr_token'] ?? null;

        if (! is_string($token) || $token === '') {
            return null;
        }

        if (strlen($token) <= 10) {
            return mb_substr($token, 0, 2).'…';
        }

        return mb_substr($token, 0, 6).'…'.mb_substr($token, -4);
    }

    // ── write ────────────────────────────────────────────────────────

    /**
     * Merge, compare, and write inside one transaction.
     *
     * Returns null when the submit changed nothing (no write, no audit, no
     * activity), or ['before' => [...], 'after' => [...]] with changed dotted
     * paths only. Throws VersionMismatchException when the stored hash moved
     * under us; a throw leaves the database untouched.
     */
    public function save(Agency $agency, array $input, string $expectedVersion, User $actor, GodAgencyService $audits): ?array
    {
        return DB::transaction(function () use ($agency, $input, $expectedVersion, $actor, $audits) {
            // Re-read inside the transaction: the document the operator looked
            // at and the document we merge onto must be the same document.
            $fresh = Agency::withoutGlobalScopes()->lockForUpdate()->find($agency->id);

            if (! $fresh) {
                throw new VersionMismatchException('This agency no longer exists. Reload the list.');
            }

            $before = $this->document($fresh);

            if ($this->hash($before) !== $expectedVersion) {
                throw new VersionMismatchException(
                    'This agency\'s settings changed while you had the form open. Reload and re-apply your changes.'
                );
            }

            $after = $this->merge($before, $input);
            // A no-op submit is not an event: no update, no audit, no activity.
            if ($this->canonical($after) === $this->canonical($before)) {
                return null;
            }

            $flatBefore = $this->flatten($before);
            $flatAfter = $this->flatten($after);

            $beforeValues = [];
            $afterValues = [];

            foreach (array_keys($this->diff($before, $after)) as $path) {
                $beforeValues[$path] = $flatBefore[$path] ?? null;
                $afterValues[$path] = $flatAfter[$path] ?? null;
            }

            Tenant::bypass(
                fn () => $fresh->update(['settings' => $after]),
                self::BYPASS_REASON,
                ['agency_id' => $agency->id]
            );

            $audits->writeAudit($fresh, self::AUDIT_ACTION_UPDATED, $beforeValues, $afterValues, $actor);
            $audits->writeActivity(
                self::AUDIT_ACTION_UPDATED,
                $fresh,
                'Settings for '.$fresh->name.' were updated in god mode ('.implode(', ', array_keys($afterValues)).').',
                $actor
            );

            return ['before' => $beforeValues, 'after' => $afterValues];
        });
    }
}
