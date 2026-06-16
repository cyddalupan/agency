<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('applicant_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('employer_cost', 12, 2)->default(0);
            $table->decimal('applicant_cost', 12, 2)->default(0);
            $table->decimal('employer_deposit', 12, 2)->default(0);
            $table->decimal('applicant_deposit', 12, 2)->default(0);
            $table->string('status')->default('pending'); // pending, less, partially_paid, paid, over_paid
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('employer_id');
            $table->index('applicant_id');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bill_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('category'); // employer_cost, applicant_cost, deposit, commission
            $table->string('type'); // cash, bank_transfer, check, gcash, online
            $table->string('reference_no')->nullable();
            $table->string('status')->default('pending'); // pending, confirmed, failed, refunded
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index('bill_id');
        });

        Schema::create('official_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('or_no')->unique();
            $table->decimal('amount', 12, 2);
            $table->date('issue_date');
            $table->string('issued_to'); // employer, applicant, agent
            $table->string('issued_to_name');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('agency_id');
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('commissionable_type')->nullable(); // marketing_agency, marketing_agent, recruitment_agent
            $table->unsignedBigInteger('commissionable_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status')->default('pending'); // pending, partial, paid
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('agency_id');
            $table->index(['commissionable_type', 'commissionable_id']);
        });

        Schema::create('marketing_agencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('contact')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('agency_id');
        });

        Schema::create('marketing_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketing_agency_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('contact')->nullable();
            $table->string('email')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('agency_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_agents');
        Schema::dropIfExists('marketing_agencies');
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('official_receipts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bills');
    }
};
