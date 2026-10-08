<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

/**
 * Renders an HTML string to PDF bytes.
 *
 * (Mjolnir "LANDAS: Resume" 2026-10-08)
 *
 * The resume must display non-Latin scripts (Arabic / Chinese / Japanese) for
 * the destination-country translation. The bundled dompdf engine cannot shape
 * Arabic (it emits reversed, disconnected glyphs), so we prefer the system
 * wkhtmltopdf binary (Qt WebKit + HarfBuzz) which shapes these scripts
 * correctly. If that binary is unavailable or fails, we transparently fall
 * back to dompdf so the endpoint never breaks.
 */
class HtmlPdfRenderer
{
    public function render(string $html, string $paper = 'a4'): string
    {
        $engine = (string) config('resume.engine', 'dompdf');
        $binary = (string) config('resume.binaries.wkhtmltopdf', '');

        if ($engine === 'wkhtmltopdf' && $binary !== '' && @is_executable($binary)) {
            $pdf = $this->renderWithWkhtmltopdf($html, $binary);

            if ($pdf !== null && $pdf !== '') {
                return $pdf;
            }

            Log::warning('HtmlPdfRenderer: wkhtmltopdf failed, falling back to dompdf.');
        }

        return Pdf::loadHtml($html)->setPaper($paper)->output(['compress' => 0]);
    }

    private function renderWithWkhtmltopdf(string $html, string $binary): ?string
    {
        $dir = storage_path('app/resume_tmp');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $base = $dir . '/' . bin2hex(random_bytes(8));
        $in = $base . '.html';
        $out = $base . '.pdf';

        if (@file_put_contents($in, $html) === false) {
            return null;
        }

        $timeout = (int) config('resume.binaries.timeout', 45);

        $cmd = 'HOME=/tmp timeout ' . escapeshellarg((string) $timeout) . ' '
            . escapeshellarg($binary)
            . ' --quiet --encoding utf-8 --enable-local-file-access'
            . ' --page-size A4'
            . ' --margin-top 0 --margin-bottom 0 --margin-left 0 --margin-right 0'
            . ' --disable-smart-shrinking'
            . ' ' . escapeshellarg($in)
            . ' ' . escapeshellarg($out)
            . ' 2>&1';

        $output = [];
        $code = 0;
        @exec($cmd, $output, $code);

        $pdf = (is_file($out) && filesize($out) > 0) ? (string) file_get_contents($out) : null;

        @unlink($in);
        @unlink($out);

        if ($pdf === null && ! empty($output)) {
            Log::warning('HtmlPdfRenderer: wkhtmltopdf output: ' . implode(' ', array_slice($output, 0, 5)));
        }

        return $pdf;
    }
}
