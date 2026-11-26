<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Card 6b07eb4f (Cyd 2026-11-26): agency -> Landas revenue ledger.
 *
 * These are CONTROL-PLANE tables. They record what an agency paid *us*, not
 * what an applicant paid the agency. There is deliberately no relating FK to
 * `receivables` and no tenant read access — so the rule in routes/web.php
 * ("money an agency collected is the agency's record") is not being violated
 * here; that rule protects the agency's books, and these rows are ours.
 *
 * Additive only: CREATE TABLE. down() drops just these two tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency_billing_plans', function (Blueprint $table) {
            $table->id();

            // restrict, not cascade: never silently orphan a money row.
            $table->foreignId('agency_id')->constrained('agencies')->restrictOnDelete();

            // Total up-front amount owed, and how many payments it takes to
            // clear it (Cyd 2026-11-26: "set how many payment before they
            // complete initial payments"). 1 = paid in one go.
            $table->decimal('initial_payment', 12, 2)->default(0);
            $table->unsignedSmallInteger('initial_terms')->default(1);

            $table->decimal('monthly_amount', 12, 2)->default(0);
            $table->unsignedSmallInteger('terms')->default(0);

            $table->char('currency', 3)->default('PHP');
            $table->date('starts_on')->nullable();
            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['agency_id', 'is_active']);
        });

        // AC7: initial_terms must be >= 1. Enforced in the database rather
        // than only in validation, so a tinker/console insert cannot create a
        // plan that claims "zero payments clear the initial".
        DB::statement('ALTER TABLE agency_billing_plans ADD CONSTRAINT chk_initial_terms_positive CHECK (initial_terms >= 1)');

        Schema::create('agency_billing_payments', function (Blueprint $table) {
            $table->id();

            // Denormalised so a payment still makes sense after a plan change.
            $table->foreignId('agency_id')->constrained('agencies')->restrictOnDelete();
            $table->foreignId('plan_id')->nullable()
                ->constrained('agency_billing_plans')->nullOnDelete();

            // 'initial' or 'monthly'. Explicit, never inferred from totals:
            // the operator says what the money was for. Re-classifying is a
            // deliberate void-and-re-record, not a silent recalculation.
            $table->string('payment_type', 16);

            $table->decimal('amount', 12, 2);
            $table->date('paid_on');
            $table->string('mode', 32);
            $table->string('reference', 120)->nullable();
            $table->string('particular', 255)->nullable();

            // Cyd's explicit requirement: who logged it.
            $table->foreignId('recorded_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['agency_id', 'paid_on']);
            // The status engine groups by type on every village render.
            $table->index(['agency_id', 'payment_type']);
        });
    }

    public function down(): void
    {
        // Payments first: they hold the FK to plans.
        Schema::dropIfExists('agency_billing_payments');
        Schema::dropIfExists('agency_billing_plans');
    }
};
