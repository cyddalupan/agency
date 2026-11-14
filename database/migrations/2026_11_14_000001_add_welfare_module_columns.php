<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WELFARE module (Mjolnir card 6af81f6a, 2026-11-14).
 *
 * The welfare table already exists from the applicant "WELFARE Tab" card. The
 * module needs two things it does not have:
 *   - a standalone record, so Welfare can be logged without opening an applicant
 *     (applicant_id therefore becomes nullable), and
 *   - the uploaded document the card asks for "below remarks".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicant_welfare_records', function (Blueprint $table) {
            $table->string('document_path', 255)->nullable()->after('remarks');
            $table->string('document_name', 255)->nullable()->after('document_path');
            $table->string('encoder', 255)->nullable()->after('logged_by');
        });

        // Welfare can now be logged on its own, not only from an applicant.
        Schema::table('applicant_welfare_records', function (Blueprint $table) {
            $table->unsignedBigInteger('applicant_id')->nullable()->change();
            $table->index(['agency_id', 'date_of_contact']);
        });
    }

    public function down(): void
    {
        Schema::table('applicant_welfare_records', function (Blueprint $table) {
            $table->dropIndex(['agency_id', 'date_of_contact']);
        });

        Schema::table('applicant_welfare_records', function (Blueprint $table) {
            $table->dropColumn(['document_path', 'document_name', 'encoder']);
        });
    }
};
