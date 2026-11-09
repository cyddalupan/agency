<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CYD 2026-11-09 (Mjolnir card "Upload and Edit"): a request can carry 2-5
 * attachments, and a Requestor may add more files on each edit while the
 * request is still Pending.
 *
 * The old single `expense_request_items.file_path` column stays as-is (one
 * legacy file per line); this table is additive and holds the multi-file set.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_request_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expense_request_id');
            $table->unsignedBigInteger('expense_request_item_id')->nullable();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->index('expense_request_id');
            $table->index('expense_request_item_id');
        });

        Schema::create('receivable_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receivable_id');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->index('receivable_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receivable_attachments');
        Schema::dropIfExists('expense_request_attachments');
    }
};
