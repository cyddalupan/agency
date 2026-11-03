<?php

/*
|--------------------------------------------------------------------------
| Custom agency domains
|--------------------------------------------------------------------------
|
| Maps a full hostname to an agency `subdomain` slug. Used by
| IdentifyAgency when an agency is served from its own domain instead of
| a subdomain of fixitautoservices.com (e.g. gulfcsdi.com -> "gulf").
|
*/

return [
    'gulfcsdi.com' => 'gulf',
    'www.gulfcsdi.com' => 'gulf',
];
