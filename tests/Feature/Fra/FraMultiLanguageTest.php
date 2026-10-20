<?php

namespace Tests\Feature\Fra;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Agency;
use App\Models\Employer;
use PHPUnit\Framework\Attributes\Test;

class FraMultiLanguageTest extends TestCase
{
    use RefreshDatabase;

    private function createEmployer(): User
    {
        // FRA logins are bound to one employer record (card "FINAS: FRA Feature").
        $agency = Agency::factory()->create(['status' => 'active']);
        $employer = Employer::factory()->create(['agency_id' => $agency->id]);

        return User::factory()->create([
            'name'        => 'Employer User',
            'email'       => 'employer@test.com',
            'user_type'   => 'employer',
            'agency_id'   => $agency->id,
            'employer_id' => $employer->id,
            'status'      => 'active',
        ]);
    }

    private function loginAsEmployer(): User
    {
        $user = $this->createEmployer();
        $this->actingAs($user);
        return $user;
    }

    // ─────────────────────────────────────────────────────
    //  Account page renders with language settings section
    // ─────────────────────────────────────────────────────

    #[Test]
    public function account_page_loads_successfully(): void
    {
        $this->loginAsEmployer();
        $response = $this->get(route('fra.account'));
        $response->assertOk();
    }

    #[Test]
    public function account_page_uses_fra_layout(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.account'))
            ->assertSee('FRA Portal')
            ->assertSee('Account');
    }

    #[Test]
    public function account_page_shows_language_settings(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.account'))
            ->assertSee(__('messages.account_settings'))
            ->assertSee(__('messages.language_settings'));
    }

    #[Test]
    public function account_page_has_language_select_input(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.account'))
            ->assertSee(__('messages.save_settings'))
            ->assertSee(__('messages.preferred_language'));
    }

    #[Test]
    public function account_page_shows_language_options(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.account'))
            ->assertSee('English')
            ->assertSee('العربية')
            ->assertSee('中文')
            ->assertSee('日本語');
    }

    #[Test]
    public function employer_can_update_language_preference(): void
    {
        $user = $this->loginAsEmployer();

        $this->post(route('fra.account.language.update'), [
            'language' => 'ar',
        ]);

        $user->refresh();
        $this->assertEquals('ar', $user->locale);

        // The account page should show Arabic as the selected/preferred language
        $this->get(route('fra.account'))
            ->assertSee('العربية')
            ->assertSee('English'); // Both options shown; Arabic is selected
    }

    #[Test]
    public function language_preference_is_persisted(): void
    {
        $user = $this->createEmployer();

        $this->actingAs($user)
            ->post(route('fra.account.language.update'), [
                'language' => 'ar',
            ]);

        $user->refresh();
        $this->assertEquals('ar', $user->locale);
    }

    #[Test]
    public function valid_languages_are_accepted(): void
    {
        $user = $this->createEmployer();
        $this->actingAs($user);

        foreach (['en', 'ar', 'zh', 'ja'] as $lang) {
            $this->post(route('fra.account.language.update'), [
                'language' => $lang,
            ]);
        }

        $user->refresh();
        $this->assertEquals('ja', $user->locale);
    }

    #[Test]
    public function invalid_language_returns_validation_error(): void
    {
        $this->loginAsEmployer();

        $this->post(route('fra.account.language.update'), [
            'language' => 'xx',
        ])->assertSessionHasErrors('language');
    }

    // ─────────────────────────────────────────────────────
    // ─────────────────────────────────────────────────────
    //  Language switcher in header navigation
    // ─────────────────────────────────────────────────────

    #[Test]
    public function dashboard_shows_fra_portal_brand(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.dashboard'))
            ->assertSee('FRA Portal');
    }

    #[Test]
    public function account_page_shows_language_in_nav(): void
    {
        $this->loginAsEmployer();
        $this->get(route('fra.account'))
            ->assertSee(__('messages.account'));
    }

    #[Test]
    public function switching_language_updates_session_locale(): void
    {
        $this->loginAsEmployer();

        $this->post(route('fra.account.language.update'), [
            'language' => 'ar',
        ]);

        $this->assertEquals('ar', app()->getLocale());
    }

    // ─────────────────────────────────────────────────────
    //  Translation files exist
    // ─────────────────────────────────────────────────────

    #[Test]
    public function english_translation_file_exists(): void
    {
        $this->assertFileExists(lang_path('en/messages.php'));
    }

    #[Test]
    public function arabic_translation_file_exists(): void
    {
        $this->assertFileExists(lang_path('ar/messages.php'));
    }

    #[Test]
    public function translation_keys_cover_header_navigation(): void
    {
        $en = require lang_path('en/messages.php');
        $ar = require lang_path('ar/messages.php');

        $keys = ['dashboard', 'line_up', 'selected', 'on_process', 'cancelled', 'account'];

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $en, "Missing English key: {$key}");
            $this->assertArrayHasKey($key, $ar, "Missing Arabic key: {$key}");
        }
    }
}
