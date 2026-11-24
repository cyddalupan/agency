<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gulf only (card 6b056b38): append-only history of payments made to an agent
 * before go-live. Additive — CREATE TABLE only; down() drops just this table.
 *
 * No unique key on purpose: two identical rows on the same day are legitimate
 * (the same applicant paid twice is a real event, not a typo).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_previous_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // encoder
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('applicant_id')->nullable()->constrained()->nullOnDelete();

            $table->date('date');
            $table->string('currency', 3)->default('PHP');
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('particular')->nullable();

            $table->timestamps();

            // Cyd 2026-11-01 precedent (agent_deductions): a delete is audited
            // (who + why), not silent. The row is soft-deleted, so the reason
            // survives on the record.
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->text('delete_reason')->nullable();

            $table->index(['agency_id', 'date']);
            $table->index(['agency_id', 'agent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_previous_payments');
    }
};
