<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Toybits 2026-09-23 — track who created an agent.
 *
 * Cyd: "paki lagay din po yung date, time and creator kung kailan po na
 * create si agent" (creator = Cyd or accounting: Mae / Evelyn).
 *
 * created_at already exists; this adds the creator (auth user) reference.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
