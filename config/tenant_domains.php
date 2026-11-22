<?php

/*
|--------------------------------------------------------------------------
| Tenant host resolution
|--------------------------------------------------------------------------
|
| Card 6b008b8dc9020955b2cc16e1. IdentifyAgency reads this config to decide
| whether a request host is the control plane, a tenant, or garbage.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Hosts that are the control plane, never a tenant
    |--------------------------------------------------------------------------
    |
    | The app is served from landas.fixitautoservices.com, while each agency
    | lives on a sibling label: gulf.fixitautoservices.com,
    | csdi.fixitautoservices.com. Reading the first label as the agency slug
    | made the main site look like an agency called "landas".
    |
    | Anything not listed here and not in `custom_domains` is treated as a
    | tenant host: it must resolve to an active agency or the request 404s.
    | APP_URL's host is included automatically, so the testing environment
    | follows its configured URL instead of a hard-coded hostname.
    */
    'main_hosts' => array_values(array_filter(array_unique(array_map(
        'strtolower',
        [
            'fixitautoservices.com',
            'www.fixitautoservices.com',
            'agency.fixitautoservices.com',
            'classapparelph.fixitautoservices.com',
            'localhost',
            '127.0.0.1',
            (string) (parse_url((string) env('APP_URL', ''), PHP_URL_HOST) ?: ''),
        ]
    )))),

    /*
    |--------------------------------------------------------------------------
    | Custom agency domains
    |--------------------------------------------------------------------------
    |
    | Maps a full hostname to an agency `subdomain` slug, for agencies served
    | from their own domain instead of a subdomain (e.g. gulfcsdi.com -> gulf).
    */
    'custom_domains' => [
        'gulfcsdi.com'     => 'gulf',
        'www.gulfcsdi.com' => 'gulf',
    ],
];
