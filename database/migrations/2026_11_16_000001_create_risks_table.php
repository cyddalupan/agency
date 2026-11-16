<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QMS-11 (card 6afa0da82ced12ad55cfdf0a): the risk register.
 *
 * Spec §12. One row per identified risk. `risk_score` and `risk_level` are
 * stored, not computed on read: the banding is a management decision recorded
 * at the time the risk was assessed, and a risk scored as High last quarter
 * must not silently become Critical because someone tuned the matrix today.
 * The model re-derives both on every save, so the stored value always matches
 * likelihood × impact under the current banding.
 *
 * `likelihood` and `impact` are 1-5. A CHECK-style guard is not expressible in
 * every MySQL version, so the range is enforced by validation and by the model;
 * the column is a tinyint to keep an out-of-range value from ever fitting by
 * accident.
 *
 * `ref_no` is unique per agency, like document codes: two agencies may each
 * have a "RISK-001", and a deleted risk must not have its reference recycled
 * onto a different risk.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();

            $table->string('ref_no', 64);

            // The process this risk belongs to, e.g. "Payroll", "Applicant vetting".
            $table->string('process_area', 128)->nullable();

            $table->string('title');

            // The spec's cause/consequence split: what makes it happen, and what
            // happens if it does. Kept apart so treatment can target the cause.
            $table->text('description')->nullable();
            $table->text('cause')->nullable();
            $table->text('consequence')->nullable();

            // 1-5 each; score and level are stored, kept in step by the model.
            $table->unsignedTinyInteger('likelihood')->default(3);
            $table->unsignedTinyInteger('impact')->default(3);
            $table->unsignedSmallInteger('risk_score')->default(9);
            $table->string('risk_level', 16)->default('medium'); // low|medium|high|critical

            $table->text('existing_controls')->nullable();
            $table->text('treatment_action')->nullable();

            $table->foreignId('risk_owner_id')->nullable()->constrained('users')->nullOnDelete();

            $table->date('target_date')->nullable();
            $table->date('review_date')->nullable();

            // open|in_treatment|closed
            $table->string('status', 32)->default('open');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'ref_no']);

            $table->index(['agency_id', 'status']);
            $table->index(['agency_id', 'risk_level']);
            $table->index(['agency_id', 'review_date']);
            $table->index('risk_owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risks');
    }
};
