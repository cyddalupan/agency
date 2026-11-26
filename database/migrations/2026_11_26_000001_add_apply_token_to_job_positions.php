<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Card 6b0798fb — "LANDAS: Job Order 'Position - Apply Now Link'".
 *
 * Every Job Order gets its own shareable marketing link. Clicking it opens the
 * public Apply Now form with that Job Order's position already fixed, so a
 * marketing post for "Chef" can never silently accept a Nurse application.
 *
 * Why a column and not the agencies.settings JSON blob the QR intake uses
 * (ApplicantQrController::SETTINGS_KEY): that blob holds ONE token per agency,
 * because a QR code only ever needs to reach "this agency's Add Applicant
 * form". This feature needs one token per ROW, the token is looked up on an
 * unauthenticated public route, and it has to be revocable per Job Order. A
 * JSON blob keyed by position id would work but makes every public lookup read
 * and decode a whole settings document, and makes rotation a read-modify-write
 * race. A nullable unique column on the row itself is the smallest correct
 * shape.
 *
 * Nullable on purpose: existing Job Orders predate the feature and must not
 * gain a link until someone actually asks for one. The token is issued on
 * first view of the Job Order page (see JobPosition::applyToken()), so a row
 * with NULL simply has never been published — it is not "invalid".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('job_positions', 'apply_token')) {
            return;
        }

        Schema::table('job_positions', function (Blueprint $table) {
            // 32 hex chars from Str::random(32)'s alphabet would be 32 chars;
            // sized 64 so a future switch to a hash-based token needs no new
            // migration. Unique so a collision is a loud error, not two Job
            // Orders silently sharing one public form.
            $table->string('apply_token', 64)->nullable()->unique()->after('status');
        });

        // Backfill nothing. Issuing tokens for all four existing Job Orders
        // would publish four public application links that nobody asked for,
        // on live data, with no way to tell afterwards which were intended.
        // They are issued lazily instead, one at a time, on first view.
    }

    public function down(): void
    {
        if (! Schema::hasColumn('job_positions', 'apply_token')) {
            return;
        }

        Schema::table('job_positions', function (Blueprint $table) {
            $table->dropUnique(['apply_token']);
            $table->dropColumn('apply_token');
        });
    }
};
