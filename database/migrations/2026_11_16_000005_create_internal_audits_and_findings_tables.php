<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QMS-17 (card 6afa0dae821b259e5f9fd4d4): the internal audit programme.
 *
 * Two tables. An audit is a planned event: what was looked at, against which
 * criteria, by whom, and when. A finding is what it turned up. A finding can
 * spawn a non-conformity, and when it does we keep the link — that is the whole
 * point of the card, because the finding is the evidence and the NC is the work
 * that follows from it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->string('ref_no', 40);
            $table->string('title');
            $table->text('scope')->nullable();
            $table->text('criteria')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->date('conducted_date')->nullable();
            $table->foreignId('lead_auditor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('planned');
            $table->text('summary')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'ref_no']);
            $table->index(['agency_id', 'status']);
        });

        Schema::create('audit_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('internal_audit_id')->constrained()->cascadeOnDelete();
            $table->string('finding_no', 40);
            $table->string('type', 20)->default('observation');
            $table->string('clause_reference')->nullable();
            $table->text('description');
            $table->text('evidence')->nullable();
            // Null until someone turns the finding into a non-conformity.
            $table->foreignId('non_conformity_id')->nullable()->constrained('non_conformities')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['internal_audit_id', 'finding_no']);
            $table->index(['agency_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_findings');
        Schema::dropIfExists('internal_audits');
    }
};
