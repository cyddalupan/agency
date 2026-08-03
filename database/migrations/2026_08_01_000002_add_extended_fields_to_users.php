<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extended user fields per Section B (User Module)
        if (! Schema::hasColumn('users', 'middle_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('middle_name')->nullable()->after('name');
            });
        }
        if (! Schema::hasColumn('users', 'surname')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('surname')->nullable()->after('middle_name');
            });
        }
        if (! Schema::hasColumn('users', 'contact')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('contact')->nullable()->after('email');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['contact', 'middle_name', 'surname']);
        });
    }
};
