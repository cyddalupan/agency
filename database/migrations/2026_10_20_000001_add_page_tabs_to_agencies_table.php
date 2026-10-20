<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Company Profile became a 4-tab page (Company Profile, Organizational Chart,
     * Recruitment Process, System User Guide). Each tab keeps its own sanitized
     * HTML, so store them as a JSON map keyed by tab slug.
     *
     * The legacy `company_profile` column is kept as the source of truth for the
     * first tab (fallback), so existing content keeps working without a rewrite.
     */
    public function up(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->json('page_tabs')->nullable()->after('company_profile');
        });
    }

    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropColumn('page_tabs');
        });
    }
};
