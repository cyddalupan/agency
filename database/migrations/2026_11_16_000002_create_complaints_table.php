<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QMS-13 (card 6afa0daa5b58fd9984641757): complaints and grievances.
 *
 * Deliberately NOT the existing `cases` model: that one is legal case tracking
 * (court, hearing, case_number) and overloading it would put statutory
 * complaint handling and litigation on the same table.
 *
 * `applicant_id` is nullable because a complaint can be made by an employer or
 * by internal staff, or by someone with no applicant record at all. It is the
 * link *when there is one*, not a requirement.
 *
 * Target dates are stored, not derived on read. The SLA they are derived from
 * is agency-configurable (agencies.settings['complaint_sla']), and if the SLA
 * changes next month an existing complaint must keep the deadline it was given
 * when it arrived. Changing the setting affects new complaints only.
 *
 * `ref_no` is unique per agency, and soft deletes keep the reference reserved,
 * matching controlled documents and risks.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();

            $table->string('ref_no', 64);

            $table->string('complainant_name');
            $table->string('complainant_type', 32)->default('applicant'); // applicant|employer|internal

            // Nullable: only set when the complainant is a known applicant.
            $table->foreignId('applicant_id')->nullable()->constrained('applicants')->nullOnDelete();

            $table->string('category')->nullable();
            $table->text('description');

            $table->date('date_received');
            $table->timestamp('acknowledged_at')->nullable();

            // Derived from the agency SLA at creation, then frozen.
            $table->date('target_ack_date')->nullable();
            $table->date('target_resolve_date')->nullable();

            $table->timestamp('resolved_at')->nullable();

            // open|acknowledged|investigating|resolved|closed
            $table->string('status', 32)->default('open');

            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->text('resolution')->nullable();
            $table->text('closure_notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'ref_no']);

            $table->index(['agency_id', 'status']);
            $table->index(['agency_id', 'date_received']);
            $table->index(['agency_id', 'target_ack_date']);
            $table->index(['agency_id', 'target_resolve_date']);
            $table->index('applicant_id');
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
