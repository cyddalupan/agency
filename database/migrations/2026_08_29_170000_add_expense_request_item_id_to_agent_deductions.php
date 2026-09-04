<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_deductions', function (Blueprint $table) {
            $table->foreignId('expense_request_item_id')
                ->nullable()
                ->after('applicant_id')
                ->constrained('expense_request_items')
                ->nullOnDelete();
            $table->index('expense_request_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('agent_deductions', function (Blueprint $table) {
            $table->dropIndex(['expense_request_item_id']);
            $table->dropConstrainedForeignId('expense_request_item_id');
        });
    }
};
