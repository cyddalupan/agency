<?php

namespace Tests\Feature\Qms;

use App\Models\ActivityLog;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6afdc2b9 — CSV export of the Sensitive Actions screen.
 *
 * Same acceptance relationship as the QMS-04 export: the exported row count
 * must equal the filtered on-screen count. The headers are this screen's own,
 * because activity_logs carries a description and metadata rather than a
 * before/after diff.
 */
class AuditTrailSensitiveExportTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->withQms()->create();
        $this->admin  = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
            'surname'   => 'Dela Cruz',
            'name'      => 'Ana',
        ]);
    }

    private function log(array $overrides = []): ActivityLog
    {
        return ActivityLog::create(array_merge([
            'agency_id'    => $this->agency->id,
            'user_id'      => $this->admin->id,
            'subject_type' => User::class,
            'subject_id'   => $this->admin->id,
            'action'       => 'login',
            'description'  => 'logged in.',
            'metadata'     => ['ip' => '203.0.113.9', 'user_agent' => 'phpunit'],
        ], $overrides));
    }

    /** @return array{0: array<string>, 1: array<int, array<string>>} */
    private function parseCsv(string $content): array
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $lines = preg_split('/\r\n|\n|\r/', trim($content));

        $rows = array_map(fn ($line) => str_getcsv($line), $lines);
        $header = array_shift($rows);

        return [$header, array_values(array_filter($rows, fn ($r) => $r !== [null] && $r !== []))];
    }

    #[Test]
    public function the_export_uses_the_sensitive_actions_column_set(): void
    {
        $this->log();

        $response = $this->actingAs($this->admin)->get(route('audit_trail.sensitive.export'));

        $response->assertOk();
        [$header] = $this->parseCsv($response->streamedContent());

        $this->assertSame([
            'Timestamp', 'Actor', 'Action', 'Description',
            'Subject type', 'Subject ID', 'IP', 'User agent',
        ], $header);
    }

    #[Test]
    public function the_export_row_count_equals_the_filtered_screen_count(): void
    {
        $this->log(['action' => 'login', 'description' => 'first']);
        $this->log(['action' => 'login', 'description' => 'second']);
        $this->log(['action' => 'failed_login', 'description' => 'third']);

        $filters = ['action' => 'login'];

        [, $rows] = $this->parseCsv(
            $this->actingAs($this->admin)
                ->get(route('audit_trail.sensitive.export', $filters))
                ->streamedContent()
        );

        $this->assertCount(2, $rows, 'The export must honour the same filters as the screen.');

        // The screen's own count for the same filter must agree.
        $screen = $this->actingAs($this->admin)
            ->get(route('audit_trail.sensitive', $filters));
        $screen->assertOk();
        $this->assertSame(2, $screen->viewData('filteredCount'));
    }

    #[Test]
    public function exported_rows_carry_actor_and_metadata_fields(): void
    {
        $this->log(['action' => 'user_password_reset_by_admin', 'description' => 'reset the password.']);

        [, $rows] = $this->parseCsv(
            $this->actingAs($this->admin)
                ->get(route('audit_trail.sensitive.export'))
                ->streamedContent()
        );

        $this->assertCount(1, $rows);
        $row = $rows[0];

        $this->assertSame('Dela Cruz, Ana', $row[1]);
        $this->assertSame('user_password_reset_by_admin', $row[2]);
        $this->assertSame('reset the password.', $row[3]);
        $this->assertSame('User', $row[4]);
        $this->assertSame('203.0.113.9', $row[6]);
        $this->assertSame('phpunit', $row[7]);
    }

    #[Test]
    public function the_export_never_includes_another_agencys_rows(): void
    {
        $this->log(['description' => 'MINE']);

        $otherAgency = Agency::factory()->withQms()->create();
        ActivityLog::create([
            'agency_id'   => $otherAgency->id,
            'action'      => 'suspicious_activity',
            'description' => 'THEIRS',
            'metadata'    => [],
        ]);

        [, $rows] = $this->parseCsv(
            $this->actingAs($this->admin)
                ->get(route('audit_trail.sensitive.export'))
                ->streamedContent()
        );

        $this->assertCount(1, $rows);
        $this->assertSame('MINE', $rows[0][3]);
    }

    #[Test]
    public function a_role_outside_the_allowlist_cannot_export(): void
    {
        $staff = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'staff',
        ]);

        $this->actingAs($staff)
            ->get(route('audit_trail.sensitive.export'))
            ->assertForbidden();
    }
}
