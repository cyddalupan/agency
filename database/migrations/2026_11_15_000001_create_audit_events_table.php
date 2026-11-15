<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QMS-01 (card 6afa0d9e19ea247279f0a1d8): the audit spine.
 *
 * A single append-only table that every material create / update / approve /
 * reject / archive / delete should write to (QMS-02 retrofits the call sites).
 * The existing `activity_logs` table is left untouched.
 *
 * Deliberately has no `updated_at`: an audit event is written once and never
 * amended. QMS-05 adds the model-level guard that blocks update/delete.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();

            // Nullable: system/scheduled actions have no authenticated actor.
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('action', 32); // created|updated|approved|rejected|archived|deleted

            $table->string('subject_type');        // e.g. App\Models\Receivable
            $table->unsignedBigInteger('subject_id');
            $table->string('subject_label')->nullable(); // human-readable, e.g. "000668"

            // Changed-fields only, not the whole model.
            $table->json('before')->nullable();
            $table->json('after')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();

            // No updated_at — append-only.
            $table->timestamp('created_at')->nullable();

            $table->index(['agency_id', 'subject_type', 'subject_id'], 'audit_events_subject_idx');
            $table->index(['agency_id', 'created_at'], 'audit_events_agency_time_idx');
            $table->index('actor_user_id');
            $table->index(['agency_id', 'action'], 'audit_events_agency_action_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
    }
};
