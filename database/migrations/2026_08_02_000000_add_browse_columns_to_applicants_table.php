<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->string('branch')->nullable()->after('agent_id');
            $table->string('encoder')->nullable()->after('branch');
            $table->string('contract')->nullable()->after('encoder');
            $table->date('contract_received_date')->nullable()->after('contract');
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn(['branch', 'encoder', 'contract', 'contract_received_date']);
        });
    }
};
