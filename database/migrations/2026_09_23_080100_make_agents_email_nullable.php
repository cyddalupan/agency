<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Toybits 2026-09-23 — email is no longer required for agents (Gulf).
 * The original agents table declared `email` NOT NULL; make it nullable.
 * (A unique index still allows many NULLs in MySQL.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
