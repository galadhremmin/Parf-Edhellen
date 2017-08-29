<?php

return [
    'title'       => env('ED_SITE_TITLE'),
    'description' => env('ED_SITE_DESCRIPTION'),
    'view_locale' => env('ED_SITE_VIEW_LOCALE'),

    // optional header. Should refer to a blade file.
    'header_view' => env('ED_HEADER_INCLUDE', ''),

    // optional footer. Should refer to a blade file.
    'footer_view' => env('ED_FOOTER_INCLUDE', ''),

    // maximimum avatar size
    'avatar_size' => env('ED_MAX_AVATAR_SIZE', 100),

    // applicable sentence builders
    'required_sentence_builders' => [
        'latin'   => App\Adapters\LatinSentenceBuilder::class,
        'tengwar' => App\Adapters\TengwarSentenceBuilder::class
    ],

    // Sitemap for unlocking sitemap view 
    'sitemap-key' => env('ED_SITEMAP_KEY', ''),

    // Logging to database?
    'system_errors_logging' => env('ED_SYSTEM_ERRORS_LOGGING', false)
];
