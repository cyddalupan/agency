<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Toybits 2026-08-16: Account Type dropdown on expense-request/create is
     * filtered by Charge + Applicant into ONE group (agent | office | applicant).
     * Adds 'applicant' as a third Charge Type and retags the APPLICANT group.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->enum('charge_type', ['office', 'agent', 'applicant'])->default('office')->change();
        });

        // Retag the APPLICANT main accounts and their sub-accounts as 'applicant'
        // so the dropdown can show them when Charge=office + Applicant picked.
        $applicantMainIds = DB::table('accounts')
            ->whereNull('parent_id')
            ->where('name', 'APPLICANT')
            ->pluck('id');

        if ($applicantMainIds->isNotEmpty()) {
            DB::table('accounts')->whereIn('id', $applicantMainIds)
                ->update(['charge_type' => 'applicant']);
            DB::table('accounts')->whereIn('parent_id', $applicantMainIds)
                ->update(['charge_type' => 'applicant']);
        }
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->enum('charge_type', ['office', 'agent'])->default('office')->change();
        });
    }
};
