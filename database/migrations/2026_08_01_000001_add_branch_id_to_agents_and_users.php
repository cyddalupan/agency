<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Branch foreign keys — nullable (label-only assignment)
        if (! Schema::hasColumn('agents', 'branch_id')) {
            Schema::table('agents', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('agency_id')
                    ->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('users', 'branch_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('agency_id')
                    ->constrained()->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
