<?php

namespace App\Models\Traits;

use App\Models\AuditEvent;
use Illuminate\Database\Eloquent\Model;

/**
 * QMS-02 (card 6afa0d9f10479ec73cce2ba3): write an AuditEvent whenever an
 * audited model is created, updated or deleted.
 *
 * Spec §26: every material create/update/approve/archive action must produce
 * an audit event. Applying this trait is how a model joins that spine.
 *
 * Deliberate choices:
 * - **Additive, not a replacement.** Models that already write their own
 *   module-history row (ReceivableHistory, expense_request_histories) keep
 *   doing so; the audit event sits alongside it.
 * - **Changed fields only.** `before`/`after` carry just the attributes that
 *   actually differ, so the Audit Trail screen can show a real diff.
 * - **Noisy columns excluded**, including secrets.
 * - **Never throws.** An audit failure must not roll back the business write:
 *   the audit is a record *of* the operation, not part of its transaction.
 *   Failures are reported to the log instead.
 * - Skips writes with no resolvable agency (impossible to attribute).
 */
trait AuditsChanges
{
    /** Attributes never included in an audit diff. */
    protected static function auditExcludedAttributes(): array
    {
        return [
            'created_at',
            'updated_at',
            'deleted_at',
            'remember_token',
            'password',
        ];
    }

    public static function bootAuditsChanges(): void
    {
        static::created(function (Model $model): void {
            static::writeAuditEvent($model, AuditEvent::ACTION_CREATED, [], $model->auditableSnapshot());
        });

        static::updated(function (Model $model): void {
            $before = [];
            $after  = [];

            foreach ($model->getChanges() as $key => $newValue) {
                if (in_array($key, static::auditExcludedAttributes(), true)) {
                    continue;
                }

                $before[$key] = $model->getOriginal($key);
                $after[$key]  = $newValue;
            }

            // Nothing meaningful changed (only timestamps) — do not write noise.
            if ($after === []) {
                return;
            }

            static::writeAuditEvent($model, AuditEvent::ACTION_UPDATED, $before, $after);
        });

        // Soft deletes fire `deleted` too; the row survives, which is what we want.
        static::deleted(function (Model $model): void {
            static::writeAuditEvent($model, AuditEvent::ACTION_DELETED, $model->auditableSnapshot(), []);
        });
    }

    /** The attribute set recorded when a record is created or deleted. */
    protected function auditableSnapshot(): array
    {
        return array_filter(
            $this->attributesToArray(),
            fn ($key) => ! in_array($key, static::auditExcludedAttributes(), true),
            ARRAY_FILTER_USE_KEY
        );
    }

    /** A short human label for the record, used in the Audit Trail list. */
    public function auditSubjectLabel(): ?string
    {
        foreach (['code', 'reference_no', 'name', 'account', 'id'] as $key) {
            $value = $this->getAttribute($key);

            if ($value !== null && $value !== '') {
                return mb_substr((string) $value, 0, 120);
            }
        }

        return null;
    }

    /**
     * The agency this record belongs to. Models without their own agency_id
     * (e.g. ExpenseRequestItem) override `resolveAuditAgencyId()`.
     */
    protected function resolveAuditAgencyId(): ?int
    {
        $agencyId = $this->getAttribute('agency_id');

        return $agencyId !== null ? (int) $agencyId : null;
    }

    protected static function writeAuditEvent(Model $model, string $action, array $before, array $after): void
    {
        try {
            $agencyId = $model->resolveAuditAgencyId();

            // Cannot attribute the event to a tenant — skip rather than write
            // an orphan that no agency scope would ever surface.
            if ($agencyId === null) {
                return;
            }

            $request = request();

            // Prefer the auth guard: in console/job/test contexts the bound
            // request carries no user, while auth() still resolves the actor
            // set by actingAs() or a real session login.
            $userId = auth()->id() ?? $request?->user()?->getKey();

            AuditEvent::withoutGlobalScopes()->create([
                'agency_id'     => $agencyId,
                'actor_user_id' => $userId,
                'action'        => $action,
                'subject_type'  => $model::class,
                'subject_id'    => $model->getKey(),
                'subject_label' => $model->auditSubjectLabel(),
                'before'        => $before ?: null,
                'after'         => $after ?: null,
                'ip_address'    => $request?->ip(),
                'user_agent'    => $request?->userAgent() ? mb_substr($request->userAgent(), 0, 512) : null,
            ]);
        } catch (\Throwable $e) {
            // An audit failure must never break the business write.
            report($e);
        }
    }
}
