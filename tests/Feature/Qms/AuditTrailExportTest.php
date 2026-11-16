<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * QMS-04 (card 6afa0da181cbe9341f046f60) — CSV export of the audit trail.
 *
 * The card's acceptance criterion is a *relationship*: the exported row count
 * must equal the filtered on-screen count. That is asserted directly by
 * parsing the CSV and comparing against the same filter's page render.
 */
class AuditTrailExportTest extends TestCase
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
        ]);
    }

    private function event(array $overrides = []): AuditEvent
    {
        return AuditEvent::withoutGlobalScopes()->create(array_merge([
            'agency_id'     => $this->agency->id,
            'actor_user_id' => $this->admin->id,
            'action'        => AuditEvent::ACTION_UPDATED,
            'subject_type'  => 'App\\Models\\Receivable',
            'subject_id'    => 1,
            'subject_label' => '000668',
            'before'        => ['status' => 'pending'],
            'after'         => ['status' => 'received'],
            'ip_address'    => '203.0.113.9',
        ], $overrides));
    }

    /** Parse a streamed CSV response into header + data rows. */
    private function parseCsv(string $content): array
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // strip BOM
        $lines = preg_split('/\r\n|\n|\r/', trim($content));

        $rows = array_map(fn ($line) => str_getcsv($line), $lines);
        $header = array_shift($rows);

        return [$header, array_values(array_filter($rows, fn ($r) => $r !== [null] && $r !== []))];
    }

    #[Test]
    public function an_admin_can_export_the_trail(): void
    {
        $this->event();

        $response = $this->actingAs($this->admin)->get(route('audit_trail.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));

        // The filename must be a real name, not the HTTP status. streamDownload's
        // second argument is the filename; passing 200 there ships "filename=200"
        // while the disposition header still looks correct.
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertMatchesRegularExpression(
            '/filename="?audit_trail_\d{8}_\d{6}\.csv"?/',
            $disposition,
            'Export filename must be audit_trail_<date>.csv, got: ' . $disposition
        );
    }

    #[Test]
    public function the_csv_carries_the_columns_the_card_names(): void
    {
        $this->event();

        $response = $this->actingAs($this->admin)->get(route('audit_trail.export'));
        [$header] = $this->parseCsv($response->streamedContent());

        $this->assertSame([
            'Timestamp', 'Actor', 'Action', 'Record type', 'Record ID',
            'Reference', 'Changed fields', 'Before', 'After', 'IP address',
        ], $header);
    }

    #[Test]
    public function the_exported_row_count_equals_the_filtered_on_screen_count(): void
    {
        $this->event(['action' => AuditEvent::ACTION_CREATED, 'subject_label' => 'AAA']);
        $this->event(['action' => AuditEvent::ACTION_UPDATED, 'subject_label' => 'BBB']);
        $this->event(['action' => AuditEvent::ACTION_UPDATED, 'subject_label' => 'CCC']);

        // On-screen count for the filter.
        $page = $this->actingAs($this->admin)
            ->get(route('audit_trail.index', ['action' => AuditEvent::ACTION_UPDATED]));
        $page->assertOk();
        $onScreen = $page->viewData('filteredCount');

        // Exported row count for the same filter.
        $csv = $this->actingAs($this->admin)
            ->get(route('audit_trail.export', ['action' => AuditEvent::ACTION_UPDATED]))
            ->streamedContent();
        [, $rows] = $this->parseCsv($csv);

        $this->assertSame(2, $onScreen);
        $this->assertCount($onScreen, $rows, 'CSV rows must equal the filtered on-screen count.');
    }

    #[Test]
    public function the_export_honours_the_free_text_filter(): void
    {
        $this->event(['subject_label' => '000668']);
        $this->event(['subject_label' => '000999']);

        $csv = $this->actingAs($this->admin)
            ->get(route('audit_trail.export', ['q' => '000668']))
            ->streamedContent();

        [, $rows] = $this->parseCsv($csv);

        $this->assertCount(1, $rows);
        $this->assertStringContainsString('000668', $rows[0][5]);
    }

    #[Test]
    public function the_export_honours_the_record_type_and_actor_filters(): void
    {
        $other = User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => 'admin',
        ]);

        $this->event(['subject_type' => 'App\\Models\\Receivable', 'subject_label' => 'REC']);
        $this->event(['subject_type' => 'App\\Models\\Payment',    'subject_label' => 'PAY']);
        $this->event(['actor_user_id' => $other->id, 'subject_label' => 'THEIRS']);

        $byType = $this->parseCsv(
            $this->actingAs($this->admin)
                ->get(route('audit_trail.export', ['subject_type' => 'App\\Models\\Payment']))
                ->streamedContent()
        )[1];
        $this->assertCount(1, $byType);

        $byActor = $this->parseCsv(
            $this->actingAs($this->admin)
                ->get(route('audit_trail.export', ['actor' => $other->id]))
                ->streamedContent()
        )[1];
        $this->assertCount(1, $byActor);
    }

    #[Test]
    public function the_changed_fields_column_lists_what_moved(): void
    {
        $this->event();

        $csv = $this->actingAs($this->admin)->get(route('audit_trail.export'))->streamedContent();
        [, $rows] = $this->parseCsv($csv);

        // Column 6 is "Changed fields".
        $this->assertSame('status', $rows[0][6]);
        $this->assertStringContainsString('pending', $rows[0][7]);   // before
        $this->assertStringContainsString('received', $rows[0][8]);  // after
    }

    #[Test]
    public function every_non_admin_role_is_blocked_from_exporting(): void
    {
        $this->event();

        foreach (['accounting', 'recruitment', 'branch', 'welfare', 'agent', 'employer'] as $role) {
            $user = User::factory()->create([
                'agency_id' => $this->agency->id,
                'user_type' => $role,
            ]);

            $this->actingAs($user)
                ->get(route('audit_trail.export'))
                ->assertForbidden();
        }
    }

    #[Test]
    public function the_screen_offers_an_export_button(): void
    {
        $this->event();

        $this->actingAs($this->admin)
            ->get(route('audit_trail.index'))
            ->assertOk()
            ->assertSee('Export CSV');
    }
}
