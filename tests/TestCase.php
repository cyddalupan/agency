<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Extract the Agent Ledger table's <tbody> HTML from a rendered page.
     *
     * The agent report page contains a filter <select> listing every agent, so
     * assertions like "this agent is absent" must be scoped to the ledger rows
     * or they match the dropdown instead. Declared once here so every test that
     * needs the scope gets the same one.
     */
    protected function ledgerTbody(string $html): string
    {
        $start = strpos($html, 'Agent Ledger');
        $this->assertNotFalse($start, 'Agent Ledger card not found in page');

        $open = strpos($html, '<tbody>', $start);
        $this->assertNotFalse($open, 'ledger <tbody> not found');

        $close = strpos($html, '</tbody>', $open);
        $this->assertNotFalse($close, 'ledger </tbody> not found');

        return substr($html, $open, $close - $open);
    }

    /**
     * Extract one ledger row's money cells, in column order:
     * commission, cash advance, backout/repat, AR, return payment, payments, balance.
     *
     * assertSeeInOrder() is NOT enough for this: it only requires the values to
     * appear in order somewhere on the page, with arbitrary content in between.
     * A figure leaking from one column into another still passes. Comparing the
     * actual cells is what pins each number to its own column.
     *
     * @return list<string> e.g. ['₱0.00','₱560.00',...,'₱560.00']
     */
    protected function ledgerRow(string $html, string $agentName): array
    {
        $tbody = $this->ledgerTbody($html);

        // Split into <tr> rows and find the one belonging to $agentName.
        preg_match_all('#<tr>(.*?)</tr>#s', $tbody, $rows);

        foreach ($rows[1] as $row) {
            if (! str_contains($row, $agentName)) {
                continue;
            }

            preg_match_all('#<td[^>]*>(.*?)</td>#s', $row, $cells);
            $cells = array_map(fn ($c) => trim(html_entity_decode(strip_tags($c))), $cells[1]);

            // Drop the leading name and branch cells; keep the seven money columns.
            return array_slice($cells, 2);
        }

        $this->fail("no ledger row found for agent {$agentName}");
    }
}
