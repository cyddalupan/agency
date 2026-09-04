<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // encoder
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('applicant_id')->nullable()->constrained()->nullOnDelete();

            $table->date('date');
            $table->string('account', 20); // Paid | Deduction
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('particular')->nullable();

            $table->timestamps();

            $table->index(['agency_id', 'date']);
            $table->index(['agency_id', 'account']);
            $table->index('agent_id');
            $table->index('applicant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_deductions');
    }
};
