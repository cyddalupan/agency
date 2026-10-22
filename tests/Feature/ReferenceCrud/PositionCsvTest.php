<?php

namespace Tests\Feature\ReferenceCrud;

use App\Models\Agency;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PositionCsvTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $agency = Agency::factory()->create();
        $this->admin = User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function export_streams_all_positions_with_category(): void
    {
        Position::factory()->create(['name' => 'Test House Helper', 'category' => 'household']);
        Position::factory()->create(['name' => 'Test Welder', 'category' => 'skilled']);

        $response = $this->actingAs($this->admin)->get(route('positions.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=utf-8');
        $body = $response->streamedContent();

        $this->assertStringContainsString('Name,Description,Category', $body);
        $this->assertStringContainsString('Test House Helper', $body);
        $this->assertStringContainsString('household', $body);
        $this->assertStringContainsString('Test Welder', $body);
    }

    #[Test]
    public function template_contains_sample_rows(): void
    {
        $response = $this->actingAs($this->admin)->get(route('positions.bulk.template'));

        $response->assertOk();
        $body = $response->streamedContent();

        $this->assertStringContainsString('Name,Description,Category', $body);
        $this->assertStringContainsString('Domestic Helper', $body);
        $this->assertStringContainsString('Welder', $body);
    }

    #[Test]
    public function bulk_import_creates_positions(): void
    {
        $csv = "Name,Description,Category\n"
            ."Test House Helper,Childcare,household\n"
            ."Test Welder,Pipe welding,\n";

        $response = $this->actingAs($this->admin)->post(route('positions.bulk.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent('positions.csv', $csv),
        ]);

        $response->assertRedirect(route('positions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('positions', ['name' => 'Test House Helper', 'category' => 'household']);
        $this->assertDatabaseHas('positions', ['name' => 'Test Welder', 'category' => 'skilled']);
    }

    #[Test]
    public function bulk_import_rejects_duplicate_names_without_inserting_anything(): void
    {
        Position::factory()->create(['name' => 'Test Welder']);

        $csv = "Name,Description,Category\n"
            ."Test House Helper,Childcare,household\n"
            ."Test Welder,Pipe welding,skilled\n";

        $response = $this->actingAs($this->admin)->post(route('positions.bulk.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent('positions.csv', $csv),
        ]);

        $response->assertSessionHas('bulk_errors');
        $this->assertDatabaseMissing('positions', ['name' => 'Test House Helper']);
        $this->assertSame(1, Position::where('name', 'Test Welder')->count());
    }

    #[Test]
    public function bulk_import_rejects_invalid_category(): void
    {
        $csv = "Name,Description,Category\nTest Nurse,Patient care,medical\n";

        $response = $this->actingAs($this->admin)->post(route('positions.bulk.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent('positions.csv', $csv),
        ]);

        $response->assertSessionHas('bulk_errors');
        $this->assertDatabaseMissing('positions', ['name' => 'Test Nurse']);
    }
}
