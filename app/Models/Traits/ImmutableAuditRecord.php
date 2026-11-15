<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * QMS-05 (card 6afa0da26845f0db92f0f9c7): make an append-only model refuse to
 * be edited or deleted through ordinary application functions (spec §26).
 *
 * Why a trait rather than a global observer: the guarantee has to live on the
 * model itself so that *every* path — a stray `->save()`, `->delete()`, a
 * `Model::where(...)->delete()` in some future controller — hits the same wall.
 * An observer can be bypassed by `withoutEvents()`; these methods cannot.
 *
 * What is blocked:
 *   - save() on a record that already exists (updates). Creating is allowed.
 *   - delete() / forceDelete() on a record that already exists.
 *   - Model::query()->delete() / forceDelete() mass deletes.
 *
 * Deliberate escape hatch — a raw, deliberate purge: `DB::table(...)` is not
 * routed through the model and is intentionally unaffected, because a true
 * retention/erasure purge (GDPR-style) must remain possible for an operator.
 * It is not reachable from any application code path today: nothing in app/
 * calls withoutAuditGuard(), and the only DB::table() write against these
 * tables is a migration's down() (schema rollback, not a runtime path).
 *
 * Deletion of *audited* business records is unaffected: the QMS-02 trait writes
 * a NEW audit event when such a record is removed, i.e. it inserts, never
 * amends. That path must keep working, and it does.
 */
trait ImmutableAuditRecord
{
    /** @var bool Lets the guard be lifted for an intentional, audited purge. */
    protected static bool $auditGuardEnabled = true;

    /**
     * Temporarily lift the guard. Reserved for an explicit, operator-driven
     * purge; deliberately verbose so it never appears in ordinary CRUD code.
     */
    public static function withoutAuditGuard(callable $callback)
    {
        static::$auditGuardEnabled = false;

        try {
            return $callback();
        } finally {
            static::$auditGuardEnabled = true;
        }
    }

    public function save(array $options = []): bool
    {
        if ($this->exists) {
            $this->refuse('updated');
        }

        return parent::save($options);
    }

    public function delete(): bool|null
    {
        $this->refuse('deleted');
    }

    public function forceDelete(): bool|null
    {
        $this->refuse('deleted');
    }

    /**
     * Mass deletes bypass model events and instance methods, so the query
     * builder must be guarded too.
     */
    protected function performDeleteOnModel(): void
    {
        $this->refuse('deleted');
    }

    /**
     * Guard the builder paths: Model::where(...)->delete().
     *
     * Eloquent's Builder::delete() checks `onDelete` *before* toBase() applies
     * global scopes, so a scope alone cannot reliably guard it: a plain
     * `Model::where(...)->delete()` would reach the unguarded branch, while
     * `withoutGlobalScopes()->delete()` would call toBase() first and only then
     * hit the guard. Attaching the guard in newQuery() covers every builder the
     * model hands out, including ones created after withoutGlobalScopes().
     */
    public static function bootImmutableAuditRecord(): void
    {
        static::deleting(function (Model $model): void {
            if ($model->exists) {
                $model->refuse('deleted');
            }
        });
    }

    /** Every builder this model returns gets the mass-delete guard. */
    public function newQuery()
    {
        return $this->guardMassDelete(parent::newQuery());
    }

    protected function guardMassDelete(Builder $builder): Builder
    {
        $builder->onDelete(function (Builder $builder) {
            if (! static::$auditGuardEnabled) {
                return $builder->toBase()->delete();
            }

            throw new RuntimeException(sprintf(
                '%s records are append-only: refusing a mass delete.',
                class_basename($builder->getModel())
            ));
        });

        return $builder;
    }

    protected function refuse(string $operation): void
    {
        if (! static::$auditGuardEnabled) {
            return;
        }

        throw new RuntimeException(sprintf(
            '%s records are append-only: refusing to be %s (record #%s). '
            . 'An audit trail that can be edited is not an audit trail.',
            class_basename($this),
            $operation,
            $this->getKey() ?? 'new'
        ));
    }
}
