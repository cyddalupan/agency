<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-agency "Company Profile" page content (sanitized HTML, WYSIWYG edited).
     * Stored on agencies so it can later be served publicly / printed outside admin.
     */
    public function up(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->longText('company_profile')->nullable()->after('settings');
        });
    }

    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropColumn('company_profile');
        });
    }
};
