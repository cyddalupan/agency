<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\ReportTemplate;
use Illuminate\Database\Seeder;

class DefaultReportTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $agencies = Agency::all();

        if ($agencies->isEmpty()) {
            $this->command->warn('No agencies found. Skipping default template creation.');

            return;
        }

        foreach ($agencies as $agency) {
            ReportTemplate::firstOrCreate(
                [
                    'agency_id' => $agency->id,
                    'name' => 'Default Applicant Report',
                ],
                [
                    'type' => 'applicant_report',
                    'config' => [
                        'columns' => ['name', 'status', 'country', 'created_at'],
                        'group_by' => null,
                        'sort_by' => 'created_at',
                        'sort_order' => 'desc',
                        'date_preset' => null,
                    ],
                    'is_active' => true,
                ]
            );
        }
    }
}
