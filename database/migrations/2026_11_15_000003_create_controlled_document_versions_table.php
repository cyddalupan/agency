<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QMS-07 (card 6afa0da4a0f4ba9d42c0560e): document versions.
 *
 * A controlled document is never edited in place; a new version row is created
 * instead, so the approved text at any point in time stays recoverable. This is
 * what makes document control auditable: "what did procedure QMS-001 actually
 * say on the day of the incident?"
 *
 * `version_no` is stored as text ("1.0", "1.1", "2.0") because a version label
 * is a label, not a number — 1.10 and 1.1 must be distinguishable, which a
 * decimal column cannot do.
 *
 * `supersedes_version_id` is a self-reference: version N points at the version
 * it replaced. It is nullable so the first version of a document has no parent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controlled_document_versions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('controlled_document_id')
                ->constrained('controlled_documents')
                ->cascadeOnDelete();

            // Denormalised agency for tenant scoping without a join.
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();

            $table->string('version_no', 16); // major.minor, e.g. "1.0"

            $table->date('effective_date')->nullable();
            $table->text('revision_summary')->nullable();

            // Self-reference: the version this one replaced.
            $table->unsignedBigInteger('supersedes_version_id')->nullable();

            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();

            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('approval_date')->nullable();

            // draft|in_review|approved|superseded|obsolete
            $table->string('status', 32)->default('draft');

            $table->timestamps();
            $table->softDeletes();

            // Two versions of the same document cannot share a label.
            $table->unique(['controlled_document_id', 'version_no'], 'cdv_document_version_unique');

            $table->index(['agency_id', 'status']);
            $table->index('supersedes_version_id');
            $table->index('controlled_document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controlled_document_versions');
    }
};
