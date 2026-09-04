<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->string('case_number', 100)->nullable()->after('title');
            $table->date('date_received')->nullable()->after('case_number');
            $table->date('date_hearing')->nullable()->after('date_received');
            $table->foreignId('employer_id')->nullable()->after('applicant_id')
                ->constrained('employers')->nullOnDelete();
            $table->string('court', 255)->nullable()->after('employer_id');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employer_id');
            $table->dropColumn(['case_number', 'date_received', 'date_hearing', 'court']);
        });
    }
};
