<?php

namespace Tests\Feature\ExpenseRequestModule;

use App\Support\Db\ExpenseRequestLiveReferenceKey;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6b038452 — the production DDL, executed on MySQL.
 *
 * The rest of the suite runs on SQLite :memory:, which honours a partial index
 * and therefore never touched the MySQL branch of the migration. That branch is
 * what actually ships, and it failed on the first attempt: the plain unique
 * (agency_id, reference_no) hit ERROR 1062 on the 303 live+soft-deleted groups
 * because MySQL does not index by predicate. This file exists so that mistake
 * is caught by a test instead of by a deploy.
 *
 * It only runs when a MySQL scratch database is explicitly provided:
 *
 *   LANDAS_MYSQL_TEST_URL=mysql://user:pass@127.0.0.1:3306/landas_scratch_linus \
 *     php artisan test --group=mysql-index
 *
 * Without it the test SKIPS. It does not substitute SQLite, and it does not
 * pretend: the skip message says the DDL is unverified.
 *
 * Destructive by design: it drops/creates the table `reference_index_probe` in
 * the given database. The database NAME is asserted to contain 'scratch' or
 * 'test' before anything runs, so a copy-paste of the production URL dies here
 * instead of dropping anything (the table is a probe name, but the rule is the
 * rule).
 */
#[Group('mysql-index')]
class ExpenseRequestReferenceIndexMysqlTest extends TestCase
{
    private const PROBE = 'reference_index_probe';

    /** MySQL identifier length limit for an index name. */
    private const INDEX = 'probe_agency_live_ref_unique';

    private const OLD_INDEX = 'probe_agency_id_reference_no_index';

    protected function setUp(): void
    {
        parent::setUp();

        $url = (string) env('LANDAS_MYSQL_TEST_URL', '');

        if ($url === '') {
            $this->markTestSkipped(
                'MySQL DDL NOT VERIFIED by this run: LANDAS_MYSQL_TEST_URL is not set. '
                . 'The migration MySQL branch (functional unique index) has not been executed. '
                . 'A human must run it against a real MySQL 8.0.13+ scratch database.'
            );
        }

        $name = parse_url($url, PHP_URL_PATH) ?: '';
        $name = trim($name, '/');

        if (stripos($name, 'scratch') === false && stripos($name, 'test') === false) {
            $this->fail("Refusing to run MySQL DDL probe against database '{$name}': the name must contain 'scratch' or 'test'.");
        }

        config([
            'database.connections.mysql_test' => [
                'driver'    => 'mysql',
                'url'       => $url,
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
                'strict'    => true,
                'engine'    => null,
            ],
        ]);

        // Name and version, printed so a green run can be told apart from a
        // run that silently reached the wrong server.
        $this->connection()->getPdo();
        fwrite(STDERR, sprintf(
            "\n[mysql-index] %s @ %s\n",
            $this->connection()->selectOne('SELECT DATABASE() AS db, VERSION() AS v')->db,
            $this->connection()->selectOne('SELECT VERSION() AS v')->v
        ));

        $this->connection()->statement('DROP TABLE IF EXISTS ' . self::PROBE);
        $this->connection()->statement(
            // The production columns the index touches, not the whole table.
            'CREATE TABLE ' . self::PROBE . ' (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                agency_id BIGINT UNSIGNED NOT NULL,
                reference_no VARCHAR(255) NOT NULL,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY ' . self::OLD_INDEX . ' (agency_id, reference_no)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    protected function tearDown(): void
    {
        if ($this->connectionOrNull() !== null) {
            $this->connection()->statement('DROP TABLE IF EXISTS ' . self::PROBE);
        }

        parent::tearDown();
    }

    private function connection(): \Illuminate\Database\Connection
    {
        return DB::connection('mysql_test');
    }

    private function connectionOrNull(): ?\Illuminate\Database\Connection
    {
        if (config('database.connections.mysql_test.url') === null) {
            return null;
        }

        try {
            return $this->connection();
        } catch (\Throwable) {
            return null;
        }
    }

    private function insert(int $agency, string $reference, ?string $deletedAt = null): void
    {
        $this->connection()->table(self::PROBE)->insert([
            'agency_id'    => $agency,
            'reference_no' => $reference,
            'deleted_at'   => $deletedAt,
        ]);
    }

    /**
     * The migration's own MySQL DDL, minus the name-specific drop of the old
     * index (the probe table uses a probe-length name).
     */
    private function applyMigrationDdl(): void
    {
        $this->connection()->statement('ALTER TABLE ' . self::PROBE . ' DROP INDEX ' . self::OLD_INDEX);
        $this->connection()->statement(
            'ALTER TABLE ' . self::PROBE . ' ADD UNIQUE INDEX ' . self::INDEX
            . ' (' . ExpenseRequestLiveReferenceKey::mysqlKeyParts(self::PROBE) . ')'
        );
    }

    private function dropMigrationDdl(): void
    {
        $this->connection()->statement('ALTER TABLE ' . self::PROBE . ' DROP INDEX ' . self::INDEX);
        $this->connection()->statement('ALTER TABLE ' . self::PROBE . ' ADD INDEX ' . self::OLD_INDEX . ' (agency_id, reference_no)');
    }

    // ------------------------------------------------------------------

    #[Test]
    public function the_ddl_builds_on_existing_live_plus_soft_deleted_groups_the_shape_of_production(): void
    {
        // Production shape: for 303 references, one live row and one
        // soft-deleted row share (agency_id, reference_no). The plain unique
        // index fails on exactly this with ERROR 1062.
        for ($i = 1; $i <= 50; $i++) {
            $this->insert(7, "ER-$i");
            $this->insert(7, "ER-$i", '2026-11-20 10:00:00');
        }

        $this->applyMigrationDdl();

        $indexes = $this->connection()->select('SHOW INDEX FROM ' . self::PROBE . ' WHERE Key_name = ?', [self::INDEX]);
        $this->assertNotEmpty($indexes, 'The unique functional index was not created.');
        $this->assertSame(0, (int) $indexes[0]->Non_unique, 'The index must be UNIQUE.');
    }

    #[Test]
    public function a_second_live_row_with_the_same_reference_is_refused(): void
    {
        $this->applyMigrationDdl();
        $this->insert(7, 'ER-1');

        $this->expectException(QueryException::class);
        $this->insert(7, 'ER-1');
    }

    #[Test]
    public function extra_soft_deleted_rows_may_keep_the_same_reference(): void
    {
        // Live + two soft-deleted copies of the same reference. The generated
        // key is NULL for all deleted rows, and MySQL never treats two NULLs
        // in a unique index as a conflict — this is the property the index
        // relies on, so it is asserted, not assumed.
        $this->applyMigrationDdl();
        $this->insert(7, 'ER-1');
        $this->insert(7, 'ER-1', '2026-10-01 00:00:00');
        $this->insert(7, 'ER-1', '2026-11-20 10:00:00');

        $this->assertSame(3, $this->connection()->table(self::PROBE)->count());
    }

    #[Test]
    public function the_same_reference_in_another_agency_is_allowed(): void
    {
        $this->applyMigrationDdl();
        $this->insert(7, 'ER-1');
        $this->insert(8, 'ER-1');

        $this->assertSame(2, $this->connection()->table(self::PROBE)->count());
    }

    #[Test]
    public function a_soft_deleted_row_may_be_restored_only_if_its_reference_is_free(): void
    {
        // A restore flips deleted_at back to NULL and therefore re-enters the
        // constraint. That is the behaviour we want: restoring into a live
        // reference must fail loudly.
        $this->applyMigrationDdl();
        $this->insert(7, 'ER-1', '2026-10-01 00:00:00');
        $this->insert(7, 'ER-2');

        // ER-1's reference is free -> restore succeeds.
        $this->connection()->table(self::PROBE)->where('reference_no', 'ER-1')
            ->update(['deleted_at' => null]);

        // A second deleted ER-2 cannot be restored while a live ER-2 exists.
        $this->insert(7, 'ER-2', '2026-10-02 00:00:00');

        $this->expectException(QueryException::class);
        $this->connection()->table(self::PROBE)->where('reference_no', 'ER-2')
            ->whereNotNull('deleted_at')->update(['deleted_at' => null]);
    }

    #[Test]
    public function down_ddl_restores_the_old_non_unique_index_and_allows_live_duplicates_again(): void
    {
        $this->applyMigrationDdl();
        $this->insert(7, 'ER-1');

        $this->dropMigrationDdl();

        $this->assertFalse(
            $this->connection()->select('SHOW INDEX FROM ' . self::PROBE . ' WHERE Key_name = ?', [self::INDEX]) !== [],
            'The unique index must be gone after down().'
        );
        $this->assertNotEmpty(
            $this->connection()->select('SHOW INDEX FROM ' . self::PROBE . ' WHERE Key_name = ?', [self::OLD_INDEX]),
            'The old non-unique index must be restored after down().'
        );

        // And it no longer constrains: this is why down() is a rollback, not a
        // safety net.
        $this->insert(7, 'ER-1');
        $this->assertSame(2, $this->connection()->table(self::PROBE)->count());
    }
}
