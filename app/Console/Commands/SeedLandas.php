<?php

namespace App\Console\Commands;

use App\Models\Applicant;
use App\Models\ApplicantCertificate;
use App\Models\ApplicantEducation;
use App\Models\ApplicantPassport;
use App\Models\ApplicantSkill;
use App\Models\ApplicantWorkExperience;
use App\Models\Bill;
use App\Models\Commission;
use App\Models\Employer;
use App\Models\JobPosition;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SeedLandas extends Command
{
    protected $signature = 'landas:seed';
    protected $description = 'Seed Landas Super with demo agency data';

    public function handle(): int
    {
        $agencyId = 1;
        $password = Hash::make('password');

        $this->info("Seeding Landas Super (Agency ID: {$agencyId})...");

        // ─── USERS ───────────────────────────────────────────────
        $users = [
            ['admin@landas.com', 'Admin User', 'admin', 'admin'],
            ['staff@landas.com', 'Staff User', 'staff', 'staff'],
            ['billing@landas.com', 'Billing User', 'billing', 'billing'],
        ];
        foreach ($users as [$email, $name, $username, $type]) {
            User::firstOrCreate(compact('email'), [
                'agency_id' => $agencyId,
                'name' => $name,
                'username' => $username,
                'password' => $password,
                'user_type' => $type,
                'status' => 'active',
            ]);
        }
        $this->info('✓ 3 agency users created');

        // ─── EMPLOYERS ───────────────────────────────────────────
        $empData = [
            ['Al-Muftah Recruitment', 'Ahmed Al-Muftah', '+966-50-123-4567', 'ahmed@almuftah.com', 'Riyadh, Saudi Arabia'],
            ['Al-Futtaim Group', 'Hassan Al-Futtaim', '+971-4-555-7890', 'hr@alfuttaim.ae', 'Dubai, UAE'],
            ['Alghanim Industries', 'Khalid Alghanim', '+965-2-222-3456', 'recruit@alghanim.com', 'Kuwait City, Kuwait'],
            ['Qatar Cool Holdings', 'Mohammed Al-Thani', '+974-4-456-7890', 'jobs@qatarcool.qa', 'Doha, Qatar'],
            ['Bahrain Petroleum Co', 'Ali Al-Khalifa', '+973-1-789-0123', 'hr@bapco.bh', 'Manama, Bahrain'],
            ['Oman Oil Marketing', 'Salim Al-Harthi', '+968-2-456-7890', 'careers@omanoil.om', 'Muscat, Oman'],
            ['Hutchison Telecom HK', 'David Wong', '+852-2-888-1234', 'hr@hthk.com.hk', 'Hong Kong'],
            ['Sembcorp Industries', 'Rajesh Kumar', '+65-6-456-7890', 'careers@sembcorp.sg', 'Singapore'],
            ['Top Glove Corp', 'Tan Sri Lim', '+60-3-789-4561', 'hr@topglove.my', 'Kuala Lumpur, Malaysia'],
            ['TSMC Taiwan', 'Dr. Mark Liu', '+886-3-567-8901', 'hr@tsmc.com.tw', 'Hsinchu, Taiwan'],
            ['Samsung Electronics', 'Kim Hyun-suk', '+82-2-2255-1234', 'recruit@samsung.com', 'Seoul, South Korea'],
            ['Mitsubishi Motors', 'Tanaka Hiroshi', '+81-3-3456-7890', 'hr@mitsubishi-motors.co.jp', 'Tokyo, Japan'],
            ['SM Investments Corp', 'John Sy', '+63-2-887-1234', 'hr@sminvestments.com', 'Pasay City, Philippines'],
        ];
        $employers = [];
        foreach ($empData as $i => [$name, $cp, $contact, $email, $address]) {
            $employers[] = Employer::firstOrCreate(compact('email'), [
                'agency_id' => $agencyId,
                'company_no' => 'CMP-'.str_pad($i+1, 4, '0', STR_PAD_LEFT),
                'name' => $name,
                'contact_person' => $cp,
                'contact' => $contact,
                'address' => $address,
                'status' => 'active',
            ]);
        }
        $this->info('✓ '.count($employers).' employers created');

        // ─── APPLICANTS ─────────────────────────────────────────
        $firstNames = ['Juan','Maria','Jose','Ana','Pedro','Elena','Carlos','Sofia','Miguel','Rosa'];
        $lastNames = ['Santos','Reyes','Cruz','Bautista','Gonzales','Mendoza','Garcia','Torres','Rivera','Fernandez'];
        $positions = ['Domestic Worker','Driver','Nurse','Engineer','Electrician','Chef','Security Guard','Teacher','Sales Clerk','Warehouse Worker'];
        $genders = ['male','female'];

        $applicants = [];
        for ($i = 0; $i < 10; $i++) {
            $first = $firstNames[array_rand($firstNames)];
            $last = $lastNames[array_rand($lastNames)];
            $a = Applicant::firstOrCreate(
                ['email' => strtolower($first.$last.$i.'@email.com')],
                [
                    'agency_id' => $agencyId,
                    'first_name' => $first,
                    'last_name' => $last,
                    'birthdate' => now()->subYears(rand(22, 50)),
                    'gender' => $genders[array_rand($genders)],
                    'contact' => '+63-9'.rand(0,9).rand(10000000, 99999999),
                    'status_code' => 0,
                    'status' => 'active',
                ]
            );
            $applicants[] = $a;

            ApplicantSkill::firstOrCreate(
                ['applicant_id' => $a->id, 'skill_name' => $positions[array_rand($positions)]],
                ['agency_id' => $agencyId]
            );
            ApplicantEducation::firstOrCreate(
                ['applicant_id' => $a->id, 'level' => 'College'],
                [
                    'agency_id' => $agencyId,
                    'school' => 'University of the Philippines',
                    'year_start' => rand(2000, 2015),
                    'year_end' => rand(2016, 2024),
                ]
            );
            ApplicantWorkExperience::firstOrCreate(
                ['applicant_id' => $a->id, 'company' => 'Sample Company Inc.'],
                [
                    'agency_id' => $agencyId,
                    'position' => $positions[array_rand($positions)],
                    'date_from' => now()->subYears(rand(1, 10)),
                    'date_to' => rand(0,1) ? now()->subMonths(rand(1, 12)) : null,
                ]
            );
            ApplicantCertificate::firstOrCreate(
                ['applicant_id' => $a->id, 'name' => $positions[array_rand($positions)].' Certification'],
                [
                    'agency_id' => $agencyId,
                    'issued_by' => 'TESDA',
                    'issue_date' => now()->subYears(rand(1, 5)),
                ]
            );
            ApplicantPassport::firstOrCreate(
                ['applicant_id' => $a->id],
                [
                    'agency_id' => $agencyId,
                    'passport_no' => 'P'.strtoupper(substr(uniqid(), -8)),
                    'issue_date' => now()->subYears(rand(1, 5)),
                    'expiry_date' => now()->addYears(rand(1, 5)),
                ]
            );
        }
        $this->info('✓ '.count($applicants).' applicants with sub-records created');

        // ─── JOB POSITIONS ──────────────────────────────────────
        $jobRoles = ['Domestic Worker','Driver','Nurse','Engineer','Electrician','Chef','Security Guard','Teacher','Sales Clerk','Warehouse Worker'];
        $positionIds = \DB::table('positions')->pluck('id')->toArray();
        $jpCount = 0;
        foreach ($jobRoles as $role) {
            $emp = $employers[array_rand($employers)];
            JobPosition::firstOrCreate(
                ['employer_id' => $emp->id, 'name' => $role],
                [
                    'agency_id' => $agencyId,
                    'position_id' => $positionIds[array_rand($positionIds)] ?? null,
                    'gender_preference' => ['male','female','any'][array_rand([0,1,2])],
                    'salary' => rand(15000, 80000),
                    'total_slots' => rand(2, 30),
                    'status' => rand(0,2) ? 'open' : 'closed',
                ]
            );
            $jpCount++;
        }
        $this->info('✓ '.$jpCount.' job positions created');

        // ─── BILLS + PAYMENTS ───────────────────────────────────
        $billCount = 0;
        $payCount = 0;
        for ($i = 0; $i < 8; $i++) {
            $emp = $employers[array_rand($employers)];
            $app = $applicants[array_rand($applicants)];
            $b = Bill::firstOrCreate(
                ['employer_id' => $emp->id, 'applicant_id' => $app->id],
                [
                    'agency_id' => $agencyId,
                    'employer_cost' => rand(30000, 150000),
                    'applicant_cost' => rand(3000, 25000),
                    'status' => ['pending','partial','paid','cancelled'][array_rand([0,1,2,3])],
                ]
            );
            if ($b->wasRecentlyCreated) {
                $billCount++;
                if (in_array($b->status, ['partial', 'paid'])) {
                    for ($j = 0; $j < rand(1, 2); $j++) {
                        Payment::create([
                            'agency_id' => $agencyId,
                            'bill_id' => $b->id,
                            'amount' => rand(5000, 50000),
                            'category' => 'employer_cost',
                            'type' => ['cash','bank_transfer','check','gcash','online'][array_rand([0,1,2,3,4])],
                            'status' => 'completed',
                            'payment_date' => now()->subDays(rand(1, 60)),
                        ]);
                        $payCount++;
                    }
                }
            }
        }
        $this->info("✓ Bills: {$billCount}, Payments: {$payCount}");

        // ─── COMMISSIONS ────────────────────────────────────────
        $comCount = 0;
        for ($i = 0; $i < 5; $i++) {
            $emp = $employers[array_rand($employers)];
            Commission::firstOrCreate(
                ['employer_id' => $emp->id, 'agency_id' => $agencyId],
                [
                    'amount' => rand(5000, 50000),
                    'paid_amount' => 0,
                    'status' => ['pending','partial','paid'][array_rand([0,1,2])],
                    'due_date' => now()->addDays(rand(1, 90)),
                ]
            );
            $comCount++;
        }
        $this->info("✓ Commissions: {$comCount}");

        $this->newLine();
        $this->line('═══════════════════════════════════════════');
        $this->line('  ✓ LANDAS SEED COMPLETE');
        $this->line('═══════════════════════════════════════════');
        $this->newLine();
        $this->line('  Super Admin → admin@agency.com / password');
        $this->line('  Admin       → admin@landas.com / password');
        $this->line('  Staff       → staff@landas.com / password');
        $this->line('  Billing     → billing@landas.com / password');

        return self::SUCCESS;
    }
}
