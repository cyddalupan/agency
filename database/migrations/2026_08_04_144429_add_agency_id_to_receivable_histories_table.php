<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receivable_histories', function (Blueprint $table) {
            $table->foreignId('agency_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->index(['agency_id', 'receivable_id']);
        });
    }

    public function down(): void
    {
        Schema::table('receivable_histories', function (Blueprint $table) {
            $table->dropIndex(['agency_id', 'receivable_id']);
            $table->dropConstrainedForeignId('agency_id');
        });
    }
};
