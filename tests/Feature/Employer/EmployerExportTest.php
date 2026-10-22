<?php

namespace Tests\Feature\Employer;

use App\Models\Agency;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmployerExportTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function export_streams_fra_list_with_login_details(): void
    {
        $agency = Agency::factory()->create();
        $admin = User::factory()->create(['agency_id' => $agency->id, 'user_type' => 'admin']);
        $employer = Employer::factory()->create([
            'agency_id' => $agency->id,
            'name' => 'Gulf Horizon Manpower Inc.',
            'email' => 'gulf@example.com',
        ]);
        User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'employer',
            'employer_id' => $employer->id,
            'email' => 'gulf@example.com',
        ]);

        $response = $this->actingAs($admin)->get(route('employers.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=utf-8');
        $body = $response->streamedContent();

        $this->assertStringContainsString('Company Name', $body);
        $this->assertStringContainsString('Gulf Horizon Manpower Inc.', $body);
        $this->assertStringContainsString('gulf@example.com', $body);
    }
}
