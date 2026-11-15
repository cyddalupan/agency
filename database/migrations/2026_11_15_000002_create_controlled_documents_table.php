<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QMS-06 (card 6afa0da3de9f235c9653dab7): the document-control spine.
 *
 * One row per controlled document. Every agency keeps its own register, so
 * `code` is unique *per agency*, not globally — two agencies may each hold
 * their own "QMS-001".
 *
 * Versions live in their own table (QMS-07), which owns `full_name` and the
 * version numbering. `current_version_id` here is a pointer to the approved
 * version, deliberately left nullable: a document exists as `draft` before it
 * has ever had a version, and without the null the create screen would have a
 * chicken-and-egg dependency on a row that does not exist yet.
 *
 * A note on the unique index: because documents are soft-deleted, a plain
 * (agency_id, code) unique index means a soft-deleted document keeps its code
 * reserved. That matches the existing `receivables` behaviour (`unique(agency_id,
 * code)` with SoftDeletes) and is the safer default for a controlled register —
 * a document code must never be silently recycled onto a different document
 * after a delete, or the audit trail would point at the wrong document.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controlled_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();

            // Code is unique per agency; enforced below.
            $table->string('code', 64);

            $table->string('title');
            $table->string('document_type')->nullable();   // procedure|policy|form|work_instruction|manual
            $table->string('department')->nullable();

            // The process owner is a person, not necessarily the author.
            $table->foreignId('process_owner_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('confidentiality', 32)->default('internal'); // public|internal|confidential|restricted

            // draft|in_review|approved|superseded|obsolete
            $table->string('status', 32)->default('draft');

            // Pointer to the approved version; filled once a version is approved.
            $table->unsignedBigInteger('current_version_id')->nullable();

            $table->date('next_review_date')->nullable();
            $table->string('review_frequency', 32)->nullable(); // monthly|quarterly|semi_annual|annual|biennial

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'code']);

            $table->index(['agency_id', 'status']);
            $table->index(['agency_id', 'next_review_date']);
            $table->index('process_owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlled_documents');
    }
};
