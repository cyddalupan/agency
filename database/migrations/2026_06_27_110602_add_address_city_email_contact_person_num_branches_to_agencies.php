<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->string('address')->nullable()->after('name');
            $table->string('city')->nullable()->after('address');
            $table->string('email')->nullable()->unique()->after('city');
            $table->string('contact_person')->nullable()->after('email');
            $table->integer('num_branches')->default(1)->after('contact_person');
        });
    }

    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropColumn(['address', 'city', 'email', 'contact_person', 'num_branches']);
        });
    }
};
