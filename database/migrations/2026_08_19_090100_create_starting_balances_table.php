<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('starting_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // encoder
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('applicant_id')->nullable()->constrained()->nullOnDelete();

            $table->date('date');
            $table->string('account', 40)->default('Starting Balance'); // fixed per spec
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('particular')->nullable();

            $table->timestamps();

            // One starting balance per agent (confirmed: "start with one per agent")
            $table->unique(['agency_id', 'agent_id']);

            $table->index(['agency_id', 'date']);
            $table->index('applicant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('starting_balances');
    }
};
