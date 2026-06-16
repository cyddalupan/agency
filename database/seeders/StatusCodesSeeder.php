<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusCodesSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [0, 'Pending', 'Pending', 'Initial registration', '#6b7280', 0],
            [1, 'For Interview', 'For Interview', 'Ready for interview', '#3b82f6', 1],
            [2, 'Interview', 'Interview', 'Currently interviewing', '#6366f1', 2],
            [3, 'For Reservation', 'For Reservation', 'Ready for reservation', '#8b5cf6', 3],
            [4, 'Reserved', 'Reserved', 'Reserved for employer', '#a855f7', 4],
            [5, 'For Selected', 'For Selected', 'Marked for selection', '#ec4899', 5],
            [6, 'Selected', 'Selected', 'Confirmed selection', '#f43f5e', 6],
            [7, 'For Deployment', 'For Deployment', 'Document processing begins', '#f97316', 7],
            [8, 'Deployed', 'Deployed', 'Deployed to employer', '#22c55e', 8],
            [9, 'For PDOS', 'For PDOS', 'Pre-Departure Orientation Seminar', '#14b8a6', 9],
            [10, 'PDOS', 'PDOS', 'PDOS completed', '#06b6d4', 10],
            [11, 'POEA Processing', 'POEA Processing', 'POEA document processing', '#0ea5e9', 11],
            [12, 'POEA Processed', 'POEA Processed', 'POEA completed', '#3b82f6', 12],
            [13, 'For OEC', 'For OEC', 'OEC processing', '#6366f1', 13],
            [14, 'OEC', 'OEC', 'OEC issued', '#8b5cf6', 14],
            [15, 'For OWWA', 'For OWWA', 'OWWA processing', '#a855f7', 15],
            [16, 'OWWA', 'OWWA', 'OWWA completed', '#d946ef', 16],
            [17, 'For Medical', 'For Medical', 'Medical exam stage', '#ec4899', 17],
            [18, 'Medical', 'Medical', 'Medical completed', '#f43f5e', 18],
            [19, 'For Visa', 'For Visa', 'Visa application', '#f97316', 19],
            [20, 'Visa', 'Visa Received', 'Visa issued', '#eab308', 20],
            [21, 'For Ticket', 'For Ticket', 'Ticket booking', '#84cc16', 21],
            [22, 'Ticket', 'Ticket', 'Ticket issued', '#22c55e', 22],
            [23, 'For Interview 2', 'For Interview 2', 'Second interview stage', '#14b8a6', 23],
            [24, 'Interview 2', 'Interview 2', 'Second interview complete', '#06b6d4', 24],
            [25, 'For Reservation 2', 'For Reservation 2', 'Second reservation', '#0ea5e9', 25],
            [26, 'Reserved 2', 'Reserved 2', 'Second reserved', '#3b82f6', 26],
            [27, 'For Selected 2', 'For Selected 2', 'Second selection', '#6366f1', 27],
            [28, 'Selected 2', 'Selected 2', 'Second selected complete', '#8b5cf6', 28],
            [29, 'For Contract', 'For Contract', 'Contract preparation', '#a855f7', 29],
            [30, 'Contract', 'Contract', 'Contract signed', '#d946ef', 30],
            [31, 'For Contract 2', 'For Contract 2', 'Second contract', '#ec4899', 31],
            [32, 'Contract 2', 'Contract 2', 'Second contract done', '#f43f5e', 32],
            [33, 'For Deployment 2', 'For Deployment 2', 'Second deployment prep', '#f97316', 33],
            [34, 'Deployed 2', 'Deployed 2', 'Second deployment done', '#eab308', 34],
            [35, 'Repatriated', 'Repatriated', 'Returned from abroad', '#ef4444', 35],
            [36, 'Blacklisted', 'Blacklisted', 'Blacklisted from system', '#dc2626', 36],
            [37, 'Banned', 'Banned', 'Banned from system', '#b91c1c', 37],
            [38, 'Cancel', 'Cancel', 'Application cancelled', '#6b7280', 38],
            [39, 'For MOFA', 'For MOFA', 'MOFA processing', '#14b8a6', 39],
            [40, 'MOFA', 'MOFA', 'MOFA completed', '#06b6d4', 40],
            [41, 'For Visa Stamping', 'For Visa Stamping', 'Visa stamping (Saudi-specific)', '#0ea5e9', 41],
            [42, 'Visa Stamped', 'Visa Stamped', 'Visa stamped', '#3b82f6', 42],
            [43, 'For Exit Clearance', 'For Exit Clearance', 'Exit clearance', '#6366f1', 43],
            [44, 'Exit Cleared', 'Exit Cleared', 'Exit clearance done', '#8b5cf6', 44],
        ];

        foreach ($statuses as $s) {
            DB::table('status_codes')->insert([
                'code'        => $s[0],
                'label'       => $s[1],
                'label_saudi' => $s[2],
                'description' => $s[3],
                'color'       => $s[4],
                'sort_order'  => $s[5],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $this->command->info('44 status codes seeded.');
    }
}
