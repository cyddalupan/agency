<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('applicants', 'branch_id')) {
            Schema::table('applicants', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('agency_id')
                    ->constrained('branches')->nullOnDelete();
            });
        }

        // Single source of truth: the branch_id dropdown replaces the free-text
        // "branch" column.
        if (Schema::hasColumn('applicants', 'branch')) {
            Schema::table('applicants', function (Blueprint $table) {
                $table->dropColumn('branch');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('applicants', 'branch_id')) {
            Schema::table('applicants', function (Blueprint $table) {
                $table->dropConstrainedForeignId('branch_id');
            });
        }

        if (! Schema::hasColumn('applicants', 'branch')) {
            Schema::table('applicants', function (Blueprint $table) {
                $table->string('branch', 255)->nullable();
            });
        }
    }
};
