<?php

namespace Database\Seeders;

use App\Models\StatusTransition;
use Illuminate\Database\Seeder;

class StatusTransitionSeeder extends Seeder
{
    public function run(): void
    {
        $transitions = [
            // === Main Pipeline (sequential) ===
            [0, 1],    // Pending -> For Interview
            [1, 2],    // For Interview -> Interview
            [2, 3],    // Interview -> For Reservation
            [3, 4],    // For Reservation -> Reserved
            [4, 5],    // Reserved -> For Selected
            [5, 6],    // For Selected -> Selected
            [6, 7],    // Selected -> For Deployment
            [7, 8],    // For Deployment -> Deployed

            // === PDOS Track ===
            [8, 9],    // Deployed -> For PDOS
            [9, 10],   // For PDOS -> PDOS

            // === POEA Track ===
            [8, 11],   // Deployed -> POEA Processing
            [11, 12],  // POEA Processing -> POEA Processed

            // === OEC Track ===
            [8, 13],   // Deployed -> For OEC
            [13, 14],  // For OEC -> OEC

            // === OWWA Track ===
            [8, 15],   // Deployed -> For OWWA
            [15, 16],  // For OWWA -> OWWA

            // === Medical Track ===
            [8, 17],   // Deployed -> For Medical
            [17, 18],  // For Medical -> Medical

            // === Visa Track ===
            [8, 19],   // Deployed -> For Visa
            [19, 20],  // For Visa -> Visa

            // === Ticket Track ===
            [8, 21],   // Deployed -> For Ticket
            [21, 22],  // For Ticket -> Ticket

            // === Second Pipeline (Interview 2 -> Deployment 2) ===
            [22, 23],  // Ticket -> For Interview 2
            [23, 24],  // For Interview 2 -> Interview 2
            [24, 25],  // Interview 2 -> For Reservation 2
            [25, 26],  // For Reservation 2 -> Reserved 2
            [26, 27],  // Reserved 2 -> For Selected 2
            [27, 28],  // For Selected 2 -> Selected 2
            [28, 29],  // Selected 2 -> For Contract

            // === Contract Track ===
            [8, 29],   // Deployed -> For Contract
            [29, 30],  // For Contract -> Contract

            // === Contract 2 Track ===
            [28, 31],  // Selected 2 -> For Contract 2
            [31, 32],  // For Contract 2 -> Contract 2
            [30, 31],  // Contract -> For Contract 2

            // === Deployment 2 ===
            [32, 33],  // Contract 2 -> For Deployment 2
            [33, 34],  // For Deployment 2 -> Deployed 2

            // === MOFA Track (Saudi-specific) ===
            [10, 39],  // PDOS -> For MOFA
            [20, 39],  // Visa -> For MOFA
            [8, 39],   // Deployed -> For MOFA
            [39, 40],  // For MOFA -> MOFA

            // === Visa Stamping Track ===
            [40, 41],  // MOFA -> For Visa Stamping
            [41, 42],  // For Visa Stamping -> Visa Stamped

            // === Exit Clearance Track ===
            [42, 43],  // Visa Stamped -> For Exit Clearance
            [43, 44],  // For Exit Clearance -> Exit Cleared

            // === Cross-track forwards ===
            [10, 11],  // PDOS -> POEA Processing
            [14, 19],  // OEC -> For Visa
            [16, 19],  // OWWA -> For Visa
            [18, 19],  // Medical -> For Visa
            [12, 19],  // POEA Processed -> For Visa
            [22, 39],  // Ticket -> For MOFA
        ];

        // Build reverse map for within-track reverse transitions
        $reverseKeys = [];
        foreach ($transitions as $t) {
            $key = $t[1] . '-' . $t[0]; // to-from key
            $reverseKeys[$key] = [$t[1], $t[0]];
        }

        // Terminal states: any active status (0-34) can go to terminal
        $terminals = [35, 36, 37, 38]; // Repatriated, Blacklisted, Banned, Cancel
        for ($from = 0; $from <= 34; $from++) {
            foreach ($terminals as $to) {
                $key = $from . '-' . $to;
                $reverseKeys[$key] = [$from, $to];
            }
        }

        $allTransitions = array_merge($transitions, array_values($reverseKeys));

        foreach ($allTransitions as $t) {
            StatusTransition::firstOrCreate([
                'from_code' => $t[0],
                'to_code'   => $t[1],
            ], ['is_active' => true]);
        }
    }
}
