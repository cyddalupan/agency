<?php

namespace Tests\Feature\Applicant;

use App\Models\Agency;
use App\Models\User;
use Database\Seeders\StatusCodesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Backout / Cancelled / Repat folder visibility — Mjolnir card
 * "Backout, Repat Module" (2026-10-06): show only to Admin and Accounting
 * users. Replaces the previous named-account ("privileged") gate.
 */
class WithdrawnRepatAccessTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StatusCodesSeeder::class);
        $this->agency = Agency::factory()->create();
    }

    private function user(string $type): User
    {
        return User::factory()->create([
            'agency_id' => $this->agency->id,
            'user_type' => $type,
        ]);
    }

    #[Test]
    #[DataProvider('allowedProvider')]
    public function allowed_roles_can_open_the_folder(string $type): void
    {
        $this->actingAs($this->user($type))
            ->get(route('applicants.withdrawn'))
            ->assertOk();
    }

    public static function allowedProvider(): array
    {
        return [['super_admin'], ['admin'], ['billing']];
    }

    #[Test]
    #[DataProvider('deniedProvider')]
    public function other_roles_are_forbidden(string $type): void
    {
        $this->actingAs($this->user($type))
            ->get(route('applicants.withdrawn'))
            ->assertForbidden();
    }

    public static function deniedProvider(): array
    {
        return [['staff'], ['processor'], ['branch'], ['paralegal'], ['operation']];
    }

    #[Test]
    public function export_is_gated_too(): void
    {
        $this->actingAs($this->user('staff'))
            ->get(route('applicants.withdrawn.export'))
            ->assertForbidden();

        $this->actingAs($this->user('billing'))
            ->get(route('applicants.withdrawn.export'))
            ->assertOk();
    }

    #[Test]
    public function sidebar_link_shows_for_admin_and_accounting_only(): void
    {
        $url = route('applicants.withdrawn');

        foreach (['super_admin', 'admin', 'billing'] as $type) {
            $html = $this->actingAs($this->user($type))
                ->get(route('applicants.index'))
                ->getContent();
            $this->assertStringContainsString($url, $html, "{$type} should see the Backout/Repat sidebar link");
        }

        foreach (['staff', 'processor', 'branch'] as $type) {
            $html = $this->actingAs($this->user($type))
                ->get(route('applicants.index'))
                ->getContent();
            $this->assertStringNotContainsString($url, $html, "{$type} should NOT see the Backout/Repat sidebar link");
        }
    }
}
