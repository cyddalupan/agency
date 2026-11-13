<?php

namespace Tests\Feature\EmployeeModule;

use App\Models\Agency;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Card 6af6fe94 — 'FINAS, CSDI: EMPLOYEE module "new"'.
 *
 * "Create an Employee module under Company Profile module ... This is only for
 * CSDI and FINAS." The tests below pin the three things that would hurt if
 * they broke: the module is reachable and works on CSDI/FINAS, it is closed on
 * every other tenant (admins included), and one agency can never read or edit
 * another agency's employee records.
 */
class EmployeeModuleTest extends TestCase
{
    use RefreshDatabase;

    private function agency(string $subdomain, string $name = 'Agency'): Agency
    {
        return Agency::factory()->create(['subdomain' => $subdomain, 'name' => $name, 'status' => 'active']);
    }

    private function admin(Agency $agency): User
    {
        return User::factory()->create([
            'agency_id' => $agency->id,
            'user_type' => 'admin',
            'status'    => 'active',
        ]);
    }

    // ---------------------------------------------------------------- access

    #[Test]
    public function csdi_and_finas_admins_can_open_the_employee_module(): void
    {
        foreach (['csdi', 'finas'] as $subdomain) {
            $user = $this->admin($this->agency($subdomain));

            $this->actingAs($user)->get(route('employees.index'))->assertOk();
            $this->actingAs($user)->get(route('employees.create'))->assertOk();
            $this->actingAs($user)->get(route('employees.settings.index'))->assertOk();
        }
    }

    #[Test]
    public function other_tenants_are_refused_even_as_admin(): void
    {
        foreach (['gulf', 'omnis'] as $subdomain) {
            $user = $this->admin($this->agency($subdomain));

            $this->actingAs($user)->get(route('employees.index'))->assertForbidden();
            $this->actingAs($user)->get(route('employees.create'))->assertForbidden();
            $this->actingAs($user)->get(route('employees.settings.index'))->assertForbidden();
        }
    }

    #[Test]
    public function the_sidebar_link_only_appears_on_csdi_and_finas(): void
    {
        $finas = $this->admin($this->agency('finas', 'Finas Manpower'));
        $gulf  = $this->admin($this->agency('gulf', 'Gulf Horizon'));

        $this->actingAs($finas)->get(route('employees.index'))
            ->assertSee('Employees')
            ->assertSee(route('employees.index'), false);

        // Gulf has no link and cannot reach it even by URL.
        $this->actingAs($gulf)->get(route('agency.dashboard'))->assertDontSee(route('employees.index'), false);
    }

    // ------------------------------------------------------------------ CRUD

    #[Test]
    public function an_employee_can_be_created_with_every_field_on_the_card(): void
    {
        $agency = $this->agency('finas');
        $branch = Branch::factory()->create(['agency_id' => $agency->id, 'name' => 'Main Office']);
        $user   = $this->admin($agency);

        $this->actingAs($user)->post(route('employees.store'), [
            'employee_no'            => 'EMP-0001',
            'full_name'              => 'Maria Santos',
            'position'               => 'Recruitment Officer',
            'department'             => 'Recruitment',
            'branch_id'              => $branch->id,
            'employment_status'      => 'Active',
            'date_hired'             => '2026-01-15',
            'educational_background' => 'BS Psychology, Cum Laude',
            'job_description'        => 'Screens and endorses qualified applicants.',
            'nbi_reference'          => 'NBI-778812',
            'nbi_issued_at'          => '2026-02-01',
            'nbi_expires_at'         => '2027-02-01',
            'training_records'       => 'QMS Awareness Orientation 2026',
            'competency_assessment'  => 'Strong on screening interviews',
            'qms_orientation'        => 'Attended 2026-01-20, acknowledged',
            'document_status'        => 'Complete',
        ])->assertRedirect(route('employees.index'));

        $employee = Employee::where('full_name', 'Maria Santos')->firstOrFail();

        $this->assertSame($agency->id, (int) $employee->agency_id);
        $this->assertSame('EMP-0001', $employee->employee_no);
        $this->assertSame('Recruitment Officer', $employee->position);
        $this->assertSame($branch->id, (int) $employee->branch_id);
        $this->assertSame('Complete', $employee->document_status);
        $this->assertSame('2027-02-01', $employee->nbi_expires_at->format('Y-m-d'));
    }

    #[Test]
    public function employment_status_and_document_status_reject_values_outside_the_card(): void
    {
        $user = $this->admin($this->agency('csdi'));

        $this->actingAs($user)->post(route('employees.store'), [
            'full_name'         => 'Bad Row',
            'employment_status' => 'Promoted',
            'document_status'   => 'Missing',
        ])->assertSessionHasErrors(['employment_status', 'document_status']);

        $this->assertSame(0, Employee::count());
    }

    #[Test]
    public function an_employee_can_be_edited_and_deleted(): void
    {
        $agency   = $this->agency('finas');
        $user     = $this->admin($agency);
        $employee = Employee::create([
            'agency_id'         => $agency->id,
            'full_name'         => 'Old Name',
            'employment_status' => 'Active',
            'document_status'   => 'Incomplete',
        ]);

        $this->actingAs($user)->put(route('employees.update', $employee), [
            'full_name'         => 'New Name',
            'employment_status' => 'Resigned',
            'document_status'   => 'Expired',
        ])->assertRedirect(route('employees.index'));

        $employee->refresh();
        $this->assertSame('New Name', $employee->full_name);
        $this->assertSame('Resigned', $employee->employment_status);
        $this->assertSame('Expired', $employee->document_status);

        $this->actingAs($user)->delete(route('employees.destroy', $employee))
            ->assertRedirect(route('employees.index'));
        $this->assertSame(0, Employee::count());
    }

    // ---------------------------------------------------- filter and tenant

    #[Test]
    public function the_table_filters_by_search_status_and_branch(): void
    {
        $agency = $this->agency('finas');
        $user   = $this->admin($agency);
        $main   = Branch::factory()->create(['agency_id' => $agency->id, 'name' => 'Main Office']);
        $cebu   = Branch::factory()->create(['agency_id' => $agency->id, 'name' => 'Cebu']);

        Employee::create(['agency_id' => $agency->id, 'full_name' => 'Ana Reyes', 'department' => 'Accounting',
            'branch_id' => $main->id, 'employment_status' => 'Active', 'document_status' => 'Complete']);
        Employee::create(['agency_id' => $agency->id, 'full_name' => 'Ben Cruz', 'department' => 'Recruitment',
            'branch_id' => $cebu->id, 'employment_status' => 'Resigned', 'document_status' => 'Expired']);

        $this->actingAs($user)->get(route('employees.index', ['q' => 'Ana']))
            ->assertSee('Ana Reyes')->assertDontSee('Ben Cruz');

        $this->actingAs($user)->get(route('employees.index', ['employment_status' => 'Resigned']))
            ->assertSee('Ben Cruz')->assertDontSee('Ana Reyes');

        $this->actingAs($user)->get(route('employees.index', ['document_status' => 'Expired']))
            ->assertSee('Ben Cruz')->assertDontSee('Ana Reyes');

        $this->actingAs($user)->get(route('employees.index', ['branch_id' => $main->id]))
            ->assertSee('Ana Reyes')->assertDontSee('Ben Cruz');
    }

    #[Test]
    public function another_agencys_employee_is_out_of_reach(): void
    {
        $finas    = $this->agency('finas');
        $csdi     = $this->agency('csdi');
        $finasUser = $this->admin($finas);

        $foreign = Employee::create([
            'agency_id'         => $csdi->id,
            'full_name'         => 'CSDI Only Person',
            'employment_status' => 'Active',
            'document_status'   => 'Complete',
        ]);

        // Not listed…
        $this->actingAs($finasUser)->get(route('employees.index'))
            ->assertOk()->assertDontSee('CSDI Only Person');

        // …and not reachable by id.
        $this->actingAs($finasUser)->get(route('employees.edit', $foreign))->assertForbidden();
        $this->actingAs($finasUser)->put(route('employees.update', $foreign), [
            'full_name' => 'Hijacked',
        ])->assertForbidden();
        $this->actingAs($finasUser)->delete(route('employees.destroy', $foreign))->assertForbidden();

        $this->assertSame('CSDI Only Person', $foreign->fresh()->full_name);
    }

    #[Test]
    public function employee_ids_are_unique_per_agency_not_globally(): void
    {
        $finas = $this->agency('finas');
        $csdi  = $this->agency('csdi');

        Employee::create(['agency_id' => $finas->id, 'employee_no' => 'EMP-0001', 'full_name' => 'Finas Person',
            'employment_status' => 'Active', 'document_status' => 'Complete']);

        // Same number, different agency — fine.
        $this->actingAs($this->admin($csdi))->post(route('employees.store'), [
            'employee_no'       => 'EMP-0001',
            'full_name'         => 'CSDI Person',
            'employment_status' => 'Active',
            'document_status'   => 'Complete',
        ])->assertSessionHasNoErrors();

        // Same number, same agency — refused.
        $this->actingAs($this->admin($finas))->post(route('employees.store'), [
            'employee_no'       => 'EMP-0001',
            'full_name'         => 'Duplicate',
            'employment_status' => 'Active',
            'document_status'   => 'Complete',
        ])->assertSessionHasErrors('employee_no');
    }

    // ------------------------------------------------------------- settings

    #[Test]
    public function employee_settings_are_per_agency_and_feed_the_form(): void
    {
        $finas = $this->agency('finas');
        $csdi  = $this->agency('csdi');
        $user  = $this->admin($finas);

        $this->actingAs($user)->post(route('employees.settings.store'), [
            'type' => 'position',
            'name' => 'Welfare Officer',
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->post(route('employees.settings.store'), [
            'type' => 'department',
            'name' => 'Documentation/Processing',
        ])->assertSessionHasNoErrors();

        $this->assertSame(2, EmployeeSetting::where('agency_id', $finas->id)->count());
        $this->assertSame(0, EmployeeSetting::where('agency_id', $csdi->id)->count());

        // The saved options show up on the Employee form as datalist suggestions.
        $this->actingAs($user)->get(route('employees.create'))
            ->assertOk()->assertSee('Welfare Officer')->assertSee('Documentation/Processing');

        // No duplicates in the same list.
        $this->actingAs($user)->post(route('employees.settings.store'), [
            'type' => 'position',
            'name' => 'Welfare Officer',
        ])->assertSessionHasErrors('name');
    }

    #[Test]
    public function a_settings_entry_from_another_agency_cannot_be_changed(): void
    {
        $finas = $this->agency('finas');
        $csdi  = $this->agency('csdi');

        $setting = EmployeeSetting::create([
            'agency_id' => $csdi->id,
            'type'      => 'position',
            'name'      => 'CSDI Position',
            'status'    => 'active',
        ]);

        $user = $this->admin($finas);

        $this->actingAs($user)->put(route('employees.settings.update', $setting), [
            'name' => 'Renamed by Finas',
        ])->assertForbidden();

        $this->actingAs($user)->delete(route('employees.settings.destroy', $setting))->assertForbidden();

        $this->assertSame('CSDI Position', $setting->fresh()->name);
    }
}
