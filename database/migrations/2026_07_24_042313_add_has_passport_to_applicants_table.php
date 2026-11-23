<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The guard must sit OUTSIDE the blueprint closure: Schema::table()
        // only collects statements, so a hasColumn() check inside it is never
        // consulted and the ADD COLUMN is always emitted. SQLite tolerated the
        // duplicate; MySQL 8 aborts with error 1060. 2026_07_24_040911 already
        // adds this column, so on a fresh chain this branch is normally a no-op.
        if (Schema::hasColumn('applicants', 'has_passport')) {
            return;
        }

        Schema::table('applicants', function (Blueprint $table) {
            $table->string('has_passport', 10)->nullable()->after('gender');
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn('has_passport');
        });
    }
};
