<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QMS-15 (card 6afa0dac333d35f77d709e61): non-conformities and CAPA.
 *
 * Spec §14. A non-conformity is a requirement that was not met, found by an
 * audit, a complaint, an incident, or by someone noticing. Its corrective
 * actions are what the agency does about it.
 *
 * Two tables rather than one because a non-conformity can need several
 * corrective actions with different owners and different due dates, and the
 * closure gate has to be judged across all of them.
 *
 * **Why the closure gate is the whole point of this card.** Spec §14 requires
 * *verification and effectiveness review* before closure, and that pair is the
 * most commonly skipped control in practice: an action is marked done, the
 * ticket is closed, and nobody ever checks whether the fix actually stopped the
 * problem recurring. The columns here exist to make skipping it impossible to
 * do quietly — `verified_at` records that someone checked the action was
 * carried out, and `effectiveness_verified_at` records that someone checked it
 * worked. They are separate dates because they answer different questions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('non_conformities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();

            $table->string('ref_no', 64);

            // where it came from: audit|complaint|incident|self_identified
            $table->string('source', 32)->default('self_identified');

            // Optional provenance when the source has a record behind it.
            $table->foreignId('complaint_id')->nullable()->constrained('complaints')->nullOnDelete();

            $table->date('detected_date');

            $table->string('process_area', 128)->nullable();

            $table->text('description');

            // The specific requirement that was not met. Kept separate from the
            // description because a finding is only testable against a clause.
            $table->text('requirement_breached')->nullable();

            $table->string('severity', 16)->default('minor'); // minor|major|critical

            // open|contained|root_cause|action_planned|verifying|closed
            $table->string('status', 32)->default('open');

            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();

            $table->date('due_date')->nullable();

            $table->timestamp('closed_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'ref_no']);

            $table->index(['agency_id', 'status']);
            $table->index(['agency_id', 'detected_date']);
            $table->index(['agency_id', 'due_date']);
            $table->index('owner_id');
        });

        Schema::create('corrective_actions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();

            $table->foreignId('non_conformity_id')
                ->constrained('non_conformities')
                ->cascadeOnDelete();

            $table->text('action_text');

            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();

            $table->date('due_date')->nullable();

            // When the action was actually carried out. Distinct from
            // verified_at: "done" and "confirmed done" are different claims.
            $table->date('completed_date')->nullable();

            // Verification: was the action implemented as described?
            $table->text('verification_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            // Effectiveness: did the action actually stop the recurrence?
            $table->text('effectiveness_notes')->nullable();
            $table->timestamp('effectiveness_verified_at')->nullable();

            // pending|in_progress|completed|verified|effective
            $table->string('status', 32)->default('pending');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['non_conformity_id', 'status']);
            $table->index(['agency_id', 'due_date']);
            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corrective_actions');
        Schema::dropIfExists('non_conformities');
    }
};
