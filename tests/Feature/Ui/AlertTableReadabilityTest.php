<?php

namespace Tests\Feature\Ui;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Mjolnir card "Error: Color white to red".
 *
 * Bulk-upload error blocks put a table inside `alert alert-error`. daisyUI's
 * table-zebra paints even rows with base-200 (near-white) while the alert's own
 * text colour stays light, so error words came out white-on-white (contrast
 * 1.12) and could not be read. app.css must keep the alert-scoped override that
 * makes every row/cell inherit the alert colour.
 */
class AlertTableReadabilityTest extends TestCase
{
    private function css(): string
    {
        return (string) file_get_contents(resource_path('css/app.css'));
    }

    #[Test]
    public function css_keeps_tables_inside_alerts_on_the_alert_colour(): void
    {
        $css = $this->css();

        $this->assertStringContainsString('READABLE TABLES INSIDE ALERTS', $css);
        $this->assertMatchesRegularExpression('/\.alert\s+table\s+td\s*\{[^}]*background-color:\s*transparent/is', $css);
        $this->assertMatchesRegularExpression('/\.alert\s+table\s+td[^{]*\{[^}]*color:\s*inherit/is', $css);
    }

    #[Test]
    public function bulk_upload_views_render_their_error_table_inside_a_coloured_alert(): void
    {
        foreach ([
            'expense_request/bulk.blade.php',
            'receivable/bulk.blade.php',
            'agents/bulk.blade.php',
            'agent_report/deduction_bulk.blade.php',
        ] as $view) {
            $path = resource_path('views/'.$view);

            if (! file_exists($path)) {
                continue;
            }

            $blade = (string) file_get_contents($path);
            $this->assertStringContainsString('alert alert-error', $blade, $view.' must colour its error block');
            $this->assertStringContainsString('table-zebra', $blade, $view.' still uses the striped error table');
        }
    }
}
