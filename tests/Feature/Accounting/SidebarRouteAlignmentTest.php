<?php

namespace Tests\Feature\Accounting;

use App\Models\Agency;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Regression tests for: "user type Accounting has its own sidebar all those
 * should work instead most of them returns 403 undefined. you should also
 * double check other user type. the sidebar and middleware or routing should
 * match properly."
 *
 * Live repro on gulf (billing probe user): sidebar showed 11 links, but
 * /applicants, /applicants/withdrawn-repat, /employers and /custom-fields
 * all returned 403 — the sidebar links were NOT gated by the same roles as
 * their route middleware.
 *
 * Fix contract: every sidebar link visible to a user type must return 200
 * for that user type, and links whose routes would 403 must be hidden.
 */
class SidebarRouteAlignmentTest extends TestCase
{
    use RefreshDatabase;

    private Agency $agency;

    /** Route middleware role lists, mirroring routes/web.php. */
    private const APPLICANTS_ROLES = ['admin', 'super_admin', 'recruiter', 'staff', 'processor', 'coordinator', 'interviewer', 'manager', 'marketer', 'director'];
    private const EMPLOYERS_ROLES  = ['admin', 'super_admin', 'staff'];
    private const FINANCE_ROLES    = ['admin', 'super_admin', 'billing'];
    private const REFERENCE_ROLES  = ['admin', 'super_admin'];

    private const ALL_USER_TYPES = [
        'super_admin', 'admin', 'billing', 'recruiter', 'staff', 'processor',
        'coordinator', 'interviewer', 'manager', 'marketer', 'director',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = Agency::factory()->create();
    }

    /** Render the sidebar for a user (agency dashboard is accessible to all types). */
    private function sidebarHtml(User $user): string
    {
        return $this->actingAs($user)
            ->get(route('agency.dashboard'))
            ->assertOk()
            ->getContent();
    }

    /**
     * The <nav> sidebar block only — page body cards (dashboard quick links)
     * are a separate surface and are not part of the sidebar contract.
     */
    private function sidebarNavHtml(string $html): string
    {
        if (preg_match('/<nav\b[^>]*>(.*?)<\/nav>/s', $html, $m)) {
            return $m[1];
        }

        return $html;
    }

    /** Extract the hrefs of every visible .sidebar-link anchor. */
    private function visibleSidebarLinks(string $html): array
    {
        preg_match_all('/<a\s[^>]*href="([^"]+)"[^>]*>/', $html, $matches);

        $links = [];
        foreach ($matches[1] as $i => $href) {
            if (str_contains($matches[0][$i], 'sidebar-link')) {
                $links[] = parse_url($href, PHP_URL_PATH) ?: $href;
            }
        }

        return array_values(array_unique($links));
    }

    private function makeUser(string $type, bool $branch = false): User
    {
        $attrs = [
            'agency_id' => $this->agency->id,
            'user_type' => $type,
        ];

        if ($branch) {
            $attrs['branch_id'] = Branch::factory()->create(['agency_id' => $this->agency->id])->id;
        }

        return User::factory()->create($attrs);
    }

    #[Test]
    #[DataProvider('userTypeProvider')]
    public function every_visible_sidebar_link_returns_200_for_the_user_type(string $userType): void
    {
        $user = $this->makeUser($userType);
        $links = $this->visibleSidebarLinks($this->sidebarHtml($user));

        $this->assertNotEmpty($links, "{$userType} user should see sidebar links");

        foreach ($links as $link) {
            $this->actingAs($user)->get($link)->assertStatus(200);
        }
    }

    public static function userTypeProvider(): array
    {
        return array_map(fn(string $type) => [$type], self::ALL_USER_TYPES);
    }

    // ---------- billing (Accounting) must not see links that 403 ----------

    #[Test]
    public function billing_user_does_not_see_applicants_or_employer_links(): void
    {
        $billing = $this->makeUser('billing');
        $html = $this->sidebarNavHtml($this->sidebarHtml($billing));

        $this->assertStringNotContainsString(route('applicants.index'), $html);
        $this->assertStringNotContainsString(route('applicants.withdrawn'), $html);
        $this->assertStringNotContainsString(route('employers.index'), $html);
    }

    #[Test]
    public function billing_user_does_not_see_custom_fields_link(): void
    {
        $billing = $this->makeUser('billing');
        $html = $this->sidebarNavHtml($this->sidebarHtml($billing));

        $this->assertStringNotContainsString(route('custom-fields.index'), $html);
    }

    #[Test]
    public function billing_user_still_sees_finance_links(): void
    {
        $billing = $this->makeUser('billing');
        $html = $this->sidebarNavHtml($this->sidebarHtml($billing));

        $this->assertStringContainsString(route('accounting.dashboard'), $html);
        $this->assertStringContainsString(route('receivable.index'), $html);
        $this->assertStringContainsString(route('expense_request.index'), $html);
    }

    // ---------- other user types ----------

    #[Test]
    public function recruiter_does_not_see_employer_link(): void
    {
        $recruiter = $this->makeUser('recruiter');
        $html = $this->sidebarNavHtml($this->sidebarHtml($recruiter));

        $this->assertStringNotContainsString(route('employers.index'), $html);
    }

    #[Test]
    public function staff_user_does_not_see_custom_fields_link(): void
    {
        $staff = $this->makeUser('staff');
        $html = $this->sidebarNavHtml($this->sidebarHtml($staff));

        $this->assertStringNotContainsString(route('custom-fields.index'), $html);
    }

    #[Test]
    public function branch_account_does_not_see_languages_or_skills_links(): void
    {
        $branchStaff = $this->makeUser('staff', branch: true);
        $html = $this->sidebarNavHtml($this->sidebarHtml($branchStaff));

        $this->assertStringNotContainsString(route('languages.index'), $html);
        $this->assertStringNotContainsString(route('skills.index'), $html);
    }

    #[Test]
    public function admin_user_sees_administrative_links(): void
    {
        $admin = $this->makeUser('admin');
        $html = $this->sidebarNavHtml($this->sidebarHtml($admin));

        $this->assertStringContainsString(route('applicants.index'), $html);
        $this->assertStringContainsString(route('employers.index'), $html);
        $this->assertStringContainsString(route('custom-fields.index'), $html);
        $this->assertStringContainsString(route('languages.index'), $html);
        $this->assertStringContainsString(route('skills.index'), $html);
    }
}
