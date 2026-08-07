<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->string('mother_name')->nullable()->after('number_of_siblings');
            $table->string('mother_occupation')->nullable()->after('mother_name');
            $table->string('father_name')->nullable()->after('mother_occupation');
            $table->string('father_occupation')->nullable()->after('father_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn(['mother_name', 'mother_occupation', 'father_name', 'father_occupation']);
        });
    }
};
