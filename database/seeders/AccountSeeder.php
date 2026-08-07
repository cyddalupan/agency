<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Agency;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Default chart-of-accounts seeded for an agency.
     * [name, type, ?parentName] — no parentName = Main; with parentName = Sub under that Main.
     * Sub accounts are intentionally free-form (the client's "open field"); these are just sensible defaults.
     */
    public const DEFAULT_MAINS = [
        ['Income', 'income'],
        ['Placement Fees', 'income', 'Income'],
        ['Referral Fees', 'income', 'Income'],
        ['Deposits', 'income', 'Income'],
        ['Office Expenses', 'expense'],
        ['Salaries', 'expense', 'Office Expenses'],
        ['Agent Advances', 'expense', 'Office Expenses', 'agent'],
        ['Commissions Paid', 'expense', 'Office Expenses'],
        ['Licenses', 'expense', 'Office Expenses'],
    ];

    public function run(?int $agencyId = null): void
    {
        $agencies = $agencyId
            ? Agency::whereKey($agencyId)->get()
            : Agency::all();

        foreach ($agencies as $agency) {
            if (Account::mains()->where('agency_id', $agency->id)->exists()) {
                continue; // already has a chart of accounts
            }

            foreach (self::DEFAULT_MAINS as $entry) {
                [$name, $type] = $entry;
                $parentName = $entry[2] ?? null;
                $chargeType = $entry[3] ?? 'office';

                $parentId = null;
                if ($parentName) {
                    $parent = Account::mains()
                        ->where('agency_id', $agency->id)
                        ->where('name', $parentName)
                        ->first();
                    $parentId = $parent?->id;
                }

                Account::create([
                    'agency_id'   => $agency->id,
                    'parent_id'   => $parentId,
                    'name'        => $name,
                    'type'        => $type,
                    'charge_type' => $chargeType,
                    'is_active'   => true,
                ]);
            }
        }

        $count = Agency::count();
        $this->command?->info("Seeded default chart of accounts for {$count} agency/ies.");
    }
}
