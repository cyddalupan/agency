<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    // ─── COMPOSITE INDEX TESTS ───────────────────────────────────────

    #[Test]
    public function applicants_has_agency_status_date_composite_index(): void
    {
        $indexes = Schema::getIndexes('applicants');

        $this->assertNotEmpty($indexes, 'No indexes found on applicants table');

        $found = $this->findIndexByColumns($indexes, ['agency_id', 'status_code', 'created_at']);

        $this->assertTrue($found,
            'Missing composite index on applicants (agency_id, status_code, created_at)'
        );
    }

    #[Test]
    public function applicants_has_agency_country_composite_index(): void
    {
        $indexes = Schema::getIndexes('applicants');

        $found = $this->findIndexByColumns($indexes, ['agency_id', 'country_id']);

        $this->assertTrue($found,
            'Missing composite index on applicants (agency_id, country_id)'
        );
    }

    #[Test]
    public function applicants_has_agency_date_composite_index(): void
    {
        $indexes = Schema::getIndexes('applicants');

        $found = $this->findIndexByColumns($indexes, ['agency_id', 'created_at']);

        $this->assertTrue($found,
            'Missing composite index on applicants (agency_id, created_at)'
        );
    }

    #[Test]
    public function bills_has_agency_employer_date_composite_index(): void
    {
        $indexes = Schema::getIndexes('bills');

        $found = $this->findIndexByColumns($indexes, ['agency_id', 'employer_id', 'created_at']);

        $this->assertTrue($found,
            'Missing composite index on bills (agency_id, employer_id, created_at)'
        );
    }

    #[Test]
    public function bills_has_agency_applicant_date_composite_index(): void
    {
        $indexes = Schema::getIndexes('bills');

        $found = $this->findIndexByColumns($indexes, ['agency_id', 'applicant_id', 'created_at']);

        $this->assertTrue($found,
            'Missing composite index on bills (agency_id, applicant_id, created_at)'
        );
    }

    #[Test]
    public function bills_has_employer_status_composite_index(): void
    {
        $indexes = Schema::getIndexes('bills');

        $found = $this->findIndexByColumns($indexes, ['employer_id', 'status']);

        $this->assertTrue($found,
            'Missing composite index on bills (employer_id, status)'
        );
    }

    #[Test]
    public function bills_has_applicant_status_composite_index(): void
    {
        $indexes = Schema::getIndexes('bills');

        $found = $this->findIndexByColumns($indexes, ['applicant_id', 'status']);

        $this->assertTrue($found,
            'Missing composite index on bills (applicant_id, status)'
        );
    }

    #[Test]
    public function payments_has_bill_cat_status_composite_index(): void
    {
        $indexes = Schema::getIndexes('payments');

        $found = $this->findIndexByColumns($indexes, ['bill_id', 'category', 'status']);

        $this->assertTrue($found,
            'Missing composite index on payments (bill_id, category, status)'
        );
    }

    #[Test]
    public function employers_has_agency_date_composite_index(): void
    {
        $indexes = Schema::getIndexes('employers');

        $found = $this->findIndexByColumns($indexes, ['agency_id', 'created_at']);

        $this->assertTrue($found,
            'Missing composite index on employers (agency_id, created_at)'
        );
    }

    #[Test]
    public function job_positions_has_employer_status_composite_index(): void
    {
        $indexes = Schema::getIndexes('job_positions');

        $found = $this->findIndexByColumns($indexes, ['employer_id', 'status']);

        $this->assertTrue($found,
            'Missing composite index on job_positions (employer_id, status)'
        );
    }

    #[Test]
    public function commissions_has_employer_status_composite_index(): void
    {
        $indexes = Schema::getIndexes('commissions');

        $found = $this->findIndexByColumns($indexes, ['employer_id', 'status']);

        $this->assertTrue($found,
            'Missing composite index on commissions (employer_id, status)'
        );
    }

    #[Test]
    public function commissions_has_poly_status_composite_index(): void
    {
        $indexes = Schema::getIndexes('commissions');

        $found = $this->findIndexByColumns($indexes, ['commissionable_type', 'commissionable_id', 'status']);

        $this->assertTrue($found,
            'Missing composite index on commissions (commissionable_type, commissionable_id, status)'
        );
    }

    // ─── FOREIGN KEY TESTS ───────────────────────────────────────────

    #[Test]
    public function applicants_has_agency_foreign_key(): void
    {
        $fks = Schema::getForeignKeys('applicants');
        $this->assertHasForeignKey($fks, 'agency_id', 'agencies', 'applicants');
    }

    #[Test]
    public function applicants_has_employer_foreign_key(): void
    {
        $fks = Schema::getForeignKeys('applicants');
        $this->assertHasForeignKey($fks, 'employer_id', 'employers', 'applicants');
    }

    #[Test]
    public function applicants_has_job_positions_foreign_key(): void
    {
        $fks = Schema::getForeignKeys('applicants');
        $this->assertHasForeignKey($fks, 'job_id', 'job_positions', 'applicants');
    }

    #[Test]
    public function bills_has_applicant_foreign_key(): void
    {
        $fks = Schema::getForeignKeys('bills');
        $this->assertHasForeignKey($fks, 'applicant_id', 'applicants', 'bills');
    }

    #[Test]
    public function bills_has_employer_foreign_key(): void
    {
        $fks = Schema::getForeignKeys('bills');
        $this->assertHasForeignKey($fks, 'employer_id', 'employers', 'bills');
    }

    #[Test]
    public function payments_has_bill_foreign_key(): void
    {
        $fks = Schema::getForeignKeys('payments');
        $this->assertHasForeignKey($fks, 'bill_id', 'bills', 'payments');
    }

    #[Test]
    public function official_receipts_has_payment_foreign_key(): void
    {
        $fks = Schema::getForeignKeys('official_receipts');
        $this->assertHasForeignKey($fks, 'payment_id', 'payments', 'official_receipts');
    }

    #[Test]
    public function commissions_has_employer_foreign_key(): void
    {
        $fks = Schema::getForeignKeys('commissions');
        $this->assertHasForeignKey($fks, 'employer_id', 'employers', 'commissions');
    }

    #[Test]
    public function commission_payments_has_commission_foreign_key(): void
    {
        $fks = Schema::getForeignKeys('commission_payments');
        $this->assertHasForeignKey($fks, 'commission_id', 'commissions', 'commission_payments');
    }

    #[Test]
    public function marketing_agents_has_marketing_agency_foreign_key(): void
    {
        $fks = Schema::getForeignKeys('marketing_agents');
        $this->assertHasForeignKey($fks, 'marketing_agency_id', 'marketing_agencies', 'marketing_agents');
    }

    #[Test]
    public function custom_field_values_has_custom_field_definition_foreign_key(): void
    {
        $fks = Schema::getForeignKeys('custom_field_values');
        $this->assertHasForeignKey($fks, 'custom_field_definition_id', 'custom_field_definitions', 'custom_field_values');
    }

    #[Test]
    public function users_has_employer_foreign_key(): void
    {
        $fks = Schema::getForeignKeys('users');
        $this->assertHasForeignKey($fks, 'employer_id', 'employers', 'users');
    }

    // ─── STATUS TRANSITIONS ─────────────────────────────────────────

    #[Test]
    public function status_transitions_has_unique_from_to_constraint(): void
    {
        if (!Schema::hasTable('status_transitions')) {
            $this->markTestSkipped('status_transitions table does not exist');
        }

        $indexes = Schema::getIndexes('status_transitions');

        $found = false;
        foreach ($indexes as $index) {
            if (!empty($index['unique']) && $index['columns'] === ['from_code', 'to_code']) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found,
            'Missing unique constraint on (from_code, to_code) in status_transitions table'
        );
    }

    // ─── AGENCY ID ON ALL TENANT TABLES ──────────────────────────────

    #[Test]
    public function all_business_tables_have_agency_id(): void
    {
        $tenantTables = [
            'applicants', 'applicant_education', 'applicant_passports', 'applicant_certificates',
            'applicant_requirements', 'applicant_work_experiences', 'applicant_skills',
            'applicant_references', 'applicant_salary_records', 'applicant_documents',
            'applicant_logs', 'employers', 'job_positions',
            'bills', 'payments', 'official_receipts', 'commissions', 'commission_payments',
            'marketing_agencies', 'marketing_agents', 'settings', 'custom_fields',
            'custom_field_definitions', 'custom_field_values',
        ];

        foreach ($tenantTables as $table) {
            $this->assertTrue(
                Schema::hasColumn($table, 'agency_id'),
                "Table {$table} is missing the agency_id column (required for multi-tenancy)"
            );
        }
    }

    #[Test]
    public function all_agency_id_columns_have_foreign_key_to_agencies(): void
    {
        $tenantTables = [
            'applicants', 'applicant_education', 'applicant_passports', 'applicant_certificates',
            'applicant_requirements', 'applicant_work_experiences', 'applicant_skills',
            'applicant_references', 'applicant_salary_records', 'applicant_documents',
            'applicant_logs', 'employers', 'job_positions',
            'bills', 'payments', 'official_receipts', 'commissions', 'commission_payments',
            'marketing_agencies', 'marketing_agents', 'settings', 'custom_fields',
            'custom_field_definitions', 'custom_field_values',
        ];

        foreach ($tenantTables as $table) {
            $fks = Schema::getForeignKeys($table);
            $this->assertHasForeignKey($fks, 'agency_id', 'agencies', $table);
        }
    }

    // ─── HELPERS ─────────────────────────────────────────────────────

    private function findIndexByColumns(array $indexes, array $expectedColumns): bool
    {
        foreach ($indexes as $index) {
            if ($index['columns'] === $expectedColumns) {
                return true;
            }
        }
        return false;
    }

    private function assertHasForeignKey(array $fks, string $column, string $referencedTable, string $tableName): void
    {
        $found = false;
        foreach ($fks as $fk) {
            $fkColumns = $fk['columns'] ?? [];
            $fkForeignTable = $fk['foreign_table'] ?? '';
            if ($fkColumns === [$column] && $fkForeignTable === $referencedTable) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found,
            "Missing foreign key: {$tableName}.{$column} → {$referencedTable}.id"
        );
    }
}
