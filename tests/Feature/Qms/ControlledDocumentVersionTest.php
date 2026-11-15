<?php

namespace Tests\Feature\Qms;

use App\Models\Agency;
use App\Models\ControlledDocument;
use App\Models\ControlledDocumentVersion;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * QMS-07 (card 6afa0da4a0f4ba9d42c0560e) — document versions.
 *
 * The acceptance criterion is the sequencing: v1.0 -> v1.1 -> v2.0, with
 * `supersedes_version_id` forming an unbroken chain back to the first version.
 */
class ControlledDocumentVersionTest extends TestCase
{
    use RefreshDatabase;

    private function document(Agency $agency, string $code = 'QMS-001'): ControlledDocument
    {
        return ControlledDocument::withoutGlobalScopes()->create([
            'agency_id'       => $agency->id,
            'code'            => $code,
            'title'           => 'Leave Policy',
            'status'          => ControlledDocument::STATUS_DRAFT,
            'confidentiality' => 'internal',
        ]);
    }

    private function version(ControlledDocument $doc, string $no, ?int $supersedes = null): ControlledDocumentVersion
    {
        return ControlledDocumentVersion::withoutGlobalScopes()->create([
            'controlled_document_id' => $doc->id,
            'agency_id'              => $doc->agency_id,
            'version_no'             => $no,
            'status'                 => ControlledDocumentVersion::STATUS_DRAFT,
            'supersedes_version_id'  => $supersedes,
        ]);
    }

    /** The acceptance criterion: 1.0 -> 1.1 -> 2.0. */
    #[Test]
    public function versions_sequence_from_1_0_to_1_1_to_2_0(): void
    {
        $this->assertSame('1.0', ControlledDocumentVersion::nextVersionNo(null));
        $this->assertSame('1.1', ControlledDocumentVersion::nextVersionNo('1.0', ControlledDocumentVersion::BUMP_MINOR));
        $this->assertSame('2.0', ControlledDocumentVersion::nextVersionNo('1.1', ControlledDocumentVersion::BUMP_MAJOR));
    }

    #[Test]
    public function minor_bumps_increment_only_the_minor_part(): void
    {
        $this->assertSame('1.2', ControlledDocumentVersion::nextVersionNo('1.1'));
        $this->assertSame('3.1', ControlledDocumentVersion::nextVersionNo('3.0'));
    }

    #[Test]
    public function a_version_label_is_text_not_a_decimal(): void
    {
        // 1.10 must not collapse into 1.1, which a float column would do.
        $this->assertSame('1.11', ControlledDocumentVersion::nextVersionNo('1.10'));
    }

    #[Test]
    public function the_full_sequence_persists_and_chains(): void
    {
        $agency = Agency::factory()->create();
        $doc = $this->document($agency);

        $v1 = $this->version($doc, '1.0');
        $v2 = $this->version($doc, '1.1', $v1->id);
        $v3 = $this->version($doc, '2.0', $v2->id);

        $this->assertSame('1.0', $v1->fresh()->version_no);
        $this->assertSame($v1->id, $v2->fresh()->supersedes_version_id);
        $this->assertSame($v2->id, $v3->fresh()->supersedes_version_id);

        // Walking the chain backwards from the newest reaches the first version.
        $chain = [];
        $cursor = $v3->fresh();
        while ($cursor !== null) {
            $chain[] = $cursor->version_no;
            $cursor = $cursor->supersedes;
        }

        $this->assertSame(['2.0', '1.1', '1.0'], $chain);
        $this->assertNull($v1->fresh()->supersedes_version_id);
    }

    #[Test]
    public function the_helper_reads_the_highest_existing_version_for_a_document(): void
    {
        $agency = Agency::factory()->create();
        $doc = $this->document($agency);

        $this->assertSame('1.0', ControlledDocumentVersion::nextVersionNoFor($doc));

        $this->version($doc, '1.0');
        $this->assertSame('1.1', ControlledDocumentVersion::nextVersionNoFor($doc));

        $this->version($doc, '1.1');
        $this->assertSame('2.0', ControlledDocumentVersion::nextVersionNoFor($doc, ControlledDocumentVersion::BUMP_MAJOR));
    }

    #[Test]
    public function two_versions_of_one_document_may_not_share_a_label(): void
    {
        $agency = Agency::factory()->create();
        $doc = $this->document($agency);

        $this->version($doc, '1.0');

        $this->expectException(QueryException::class);

        $this->version($doc, '1.0');
    }

    #[Test]
    public function two_documents_may_each_have_a_version_1_0(): void
    {
        $agency = Agency::factory()->create();

        $docA = $this->document($agency, 'QMS-001');
        $docB = $this->document($agency, 'QMS-002');

        $a = $this->version($docA, '1.0');
        $b = $this->version($docB, '1.0');

        $this->assertNotSame($a->id, $b->id);
        $this->assertSame('1.0', $a->fresh()->version_no);
        $this->assertSame('1.0', $b->fresh()->version_no);
    }

    #[Test]
    public function relations_both_ways_resolve(): void
    {
        $agency = Agency::factory()->create();
        $doc = $this->document($agency);

        $this->version($doc, '1.0');
        $this->version($doc, '1.1');

        $this->assertCount(2, $doc->fresh()->versions);

        $version = ControlledDocumentVersion::withoutGlobalScopes()->first();
        $this->assertSame($doc->id, $version->document->id);
    }

    #[Test]
    public function full_name_combines_code_title_and_version(): void
    {
        $agency = Agency::factory()->create();
        $doc = $this->document($agency, 'QMS-001');

        $v = $this->version($doc, '1.1');

        $this->assertSame('QMS-001 Leave Policy — v1.1', $v->fresh()->full_name);
    }

    #[Test]
    public function current_version_relation_points_at_the_chosen_version(): void
    {
        $agency = Agency::factory()->create();
        $doc = $this->document($agency);

        $v1 = $this->version($doc, '1.0');
        $v2 = $this->version($doc, '1.1');

        $doc->update(['current_version_id' => $v2->id]);

        $this->assertSame($v2->id, $doc->fresh()->currentVersion->id);
        // v1 exists and is not the current one.
        $this->assertNotNull($v1->fresh());
    }

    #[Test]
    public function every_status_constant_is_labelled_and_badged(): void
    {
        foreach (ControlledDocumentVersion::STATUSES as $status) {
            $this->assertArrayHasKey($status, ControlledDocumentVersion::STATUS_LABELS);
            $this->assertArrayHasKey($status, ControlledDocumentVersion::STATUS_BADGES);
        }
    }
}
