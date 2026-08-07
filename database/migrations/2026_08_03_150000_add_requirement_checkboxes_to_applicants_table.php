<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->boolean('e_reg')->default(false);
            $table->boolean('peos')->default(false);
            $table->boolean('info_sheet')->default(false);
            $table->boolean('birth_certificate')->default(false);
            $table->boolean('marriage_certificate')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn(['e_reg', 'peos', 'info_sheet', 'birth_certificate', 'marriage_certificate']);
        });
    }
};
