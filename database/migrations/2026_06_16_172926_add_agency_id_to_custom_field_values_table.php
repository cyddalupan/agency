<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_field_values', function (Blueprint $table) {
            // Add agency_id for multi-tenant scoping (denormalized for TenantScope)
            $table->foreignId('agency_id')
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();

            // Composite index for tenant-scoped queries
            $table->index(
                ['agency_id', 'custom_field_definition_id', 'model_type', 'model_id'],
                'idx_cfv_tenant_scope'
            );
        });
    }

    public function down(): void
    {
        Schema::table('custom_field_values', function (Blueprint $table) {
            $table->dropIndex('idx_cfv_tenant_scope');
            $table->dropForeign(['agency_id']);
            $table->dropColumn('agency_id');
        });
    }
};
