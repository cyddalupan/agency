<?php

namespace Tests\Feature\Portal;

use App\Models\Applicant;
use App\Models\ApplicantLog;
use App\Models\Agency;
use App\Models\StatusCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicantStatusTimelineTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;
    private Applicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();

        $this->applicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);
    }

    #[Test]
    public function dashboard_shows_status_timeline_section(): void
    {
        ApplicantLog::create([
            'agency_id'     => $this->agency->id,
            'applicant_id'  => $this->applicant->id,
            'user_id'       => null,
            'old_status'    => null,
            'new_status'    => 'new',
            'notes'         => 'Application submitted',
        ]);

        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.dashboard'));

        $response->assertOk();
        $response->assertSee('Status History');
        $response->assertSee('Application submitted');
        $response->assertSee('new');
    }

    #[Test]
    public function status_logs_are_in_reverse_chronological_order(): void
    {
        ApplicantLog::query()->where('applicant_id', $this->applicant->id)->delete();

        $first = ApplicantLog::create([
            'agency_id'     => $this->agency->id,
            'applicant_id'  => $this->applicant->id,
            'user_id'       => null,
            'new_status'    => 'first',
        ]);
        $first->created_at = now()->subDays(3);
        $first->save();

        $second = ApplicantLog::create([
            'agency_id'     => $this->agency->id,
            'applicant_id'  => $this->applicant->id,
            'user_id'       => null,
            'new_status'    => 'second',
        ]);
        $second->created_at = now()->subDay();
        $second->save();

        $third = ApplicantLog::create([
            'agency_id'     => $this->agency->id,
            'applicant_id'  => $this->applicant->id,
            'user_id'       => null,
            'new_status'    => 'third',
        ]);
        $third->created_at = now();
        $third->save();

        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.dashboard'));

        $response->assertOk();
        $response->assertSeeInOrder(['third', 'second', 'first']);
    }

    #[Test]
    public function timeline_shows_only_current_applicant_logs(): void
    {
        ApplicantLog::query()->where('applicant_id', $this->applicant->id)->delete();

        ApplicantLog::create([
            'agency_id'     => $this->agency->id,
            'applicant_id'  => $this->applicant->id,
            'user_id'       => null,
            'new_status'    => 'my-log',
        ]);

        $otherApplicant = Applicant::factory()->create([
            'agency_id' => $this->agency->id,
        ]);
        ApplicantLog::create([
            'agency_id'     => $this->agency->id,
            'applicant_id'  => $otherApplicant->id,
            'user_id'       => null,
            'new_status'    => 'other-applicant-log',
        ]);

        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.dashboard'));

        $response->assertOk();
        $response->assertSee('my-log');
        $response->assertDontSee('other-applicant-log');
    }

    #[Test]
    public function timeline_handles_empty_logs_gracefully(): void
    {
        ApplicantLog::query()->where('applicant_id', $this->applicant->id)->delete();

        $response = $this->actingAs($this->applicant, 'applicant')
            ->get(route('portal.dashboard'));

        $response->assertOk();
        $response->assertSee('Status History');
    }
}
