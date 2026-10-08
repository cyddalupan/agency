<?php

/*
|--------------------------------------------------------------------------
| Resume (CV) rendering configuration
|--------------------------------------------------------------------------
|
| (Mjolnir "LANDAS: Resume" 2026-10-08)
|
| The resume is a bilingual "bio-data" document. Field labels are shown in
| English and in the language of the destination country (the country of the
| FRA / foreign employer). Mapping of country -> locale lives here.
|
*/

return [

    /*
    | PDF engine used for the resume.
    |
    | "wkhtmltopdf" (Qt WebKit + HarfBuzz) correctly shapes Arabic and CJK
    | scripts, which the default dompdf engine cannot (dompdf reverses and
    | disconnects Arabic glyphs). When set to anything else, or when the
    | binary is missing, rendering falls back to the bundled dompdf engine.
    */
    'engine' => env('RESUME_PDF_ENGINE', 'wkhtmltopdf'),

    'binaries' => [
        'wkhtmltopdf' => env('WKHTMLTOPDF_BIN', '/usr/bin/wkhtmltopdf'),
        'timeout'     => (int) env('RESUME_PDF_TIMEOUT', 45),
    ],

    /*
    | ISO-3166 alpha-2 destination/FRA country code -> label locale.
    | Countries not listed fall back to the application locale (English).
    */
    'locale_by_country' => [
        // Arabic-speaking destinations
        'SA' => 'ar', 'AE' => 'ar', 'KW' => 'ar', 'QA' => 'ar',
        'OM' => 'ar', 'BH' => 'ar', 'JO' => 'ar', 'EG' => 'ar',
        // Chinese-speaking destinations
        'CN' => 'zh', 'TW' => 'zh', 'HK' => 'zh',
        // Japanese-speaking destinations
        'JP' => 'ja',
    ],
];
