<?php

return [
    'version'         => env('ED_VERSION', 1),
    'title'           => env('ED_SITE_TITLE'),
    'description'     => env('ED_SITE_DESCRIPTION'),
    'view_locale'     => env('ED_SITE_VIEW_LOCALE'),

    // optional header. Should refer to a blade file.
    'header_view'     => env('ED_HEADER_INCLUDE', ''),
    'jumbotron_files' => ! empty(env('ED_SITE_JUMBOTRON_FILES', '')) 
        ? explode(',', env('ED_SITE_JUMBOTRON_FILES')) : [],

    // optional footer. Should refer to a blade file.
    'footer_view'     => env('ED_FOOTER_INCLUDE', ''),

    // maximimum avatar size
    'avatar_size'     => env('ED_MAX_AVATAR_SIZE', 100),

    // applicable sentence builders
    'required_sentence_builders' => [
        'latin'   => App\Adapters\LatinSentenceBuilder::class,
        'tengwar' => App\Adapters\TengwarSentenceBuilder::class
    ],

    // Sitemap for unlocking sitemap view 
    'sitemap_key' => env('ED_SITEMAP_KEY', ''),

    // Logging to database?
    'system_errors_logging' => env('ED_SYSTEM_ERRORS_LOGGING', false),

    'forum_entities' => [
        App\Models\Account::class      => App\Http\Discuss\Contexts\AccountContext::class,
        App\Models\Contribution::class => App\Http\Discuss\Contexts\ContributionContext::class,
        App\Models\Sentence::class     => App\Http\Discuss\Contexts\SentenceContext::class,
        App\Models\Translation::class  => App\Http\Discuss\Contexts\TranslationContext::class
    ],
    'forum_resultset_max_length' => 10
];
