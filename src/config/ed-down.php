<?php

return [

    /*
    |--------------------------------------------------------------------------
    | What the maintenance page says
    |--------------------------------------------------------------------------
    |
    | The page is baked once, by `php artisan down --render="errors::503"`, so
    | everything here is read in that one process and nothing changes until the
    | command runs again.
    |
    */

    // Announced under "New in this binding", three at most. Empty, and the page
    // promises nothing -- which is what an unplanned outage should do.
    'coming' => array_values(array_filter(array_map(
        'trim',
        explode('|', (string) env('ED_DOWN_COMING', ''))
    ))),

    // Where word of an outage is given as it happens.
    'x_handle' => env('ED_DOWN_X_HANDLE', 'parmaeldo'),

    // One is drawn per outage, so two deploys running are not the same leaf.
    // Attested words only, with the source a reader could look up.
    'words' => [
        [
            'word' => 'envinyatar',
            'language' => 'Quenya',
            'language_tag' => 'qya',
            'speech' => 'noun',
            'source' => 'LotR/V:8',
            'gloss' => 'renewer, one who makes new again',
        ],
        [
            'word' => 'vinya',
            'language' => 'Quenya',
            'language_tag' => 'qya',
            'speech' => 'adjective',
            'source' => 'S/Index',
            'gloss' => 'new, fresh, young',
        ],
        [
            'word' => 'carweg',
            'language' => 'Sindarin',
            'language_tag' => 'sjn',
            'speech' => 'adjective',
            'source' => 'PE17/144',
            'gloss' => 'active; busy',
        ],
    ],

];
