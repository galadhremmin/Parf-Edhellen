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

    // maximum characters in the account field
    'max_nickname_length' => env('ED_MAX_NICKNAME_LENGTH', 64),

    // default account name for new accounts
    'default_account_name' => env('ED_DEFAULT_ACCOUNT_NAME', 'Account'),

    // maximum number of translations per gloss
    'max_number_of_translations' => env('ED_MAX_TRANSLATIONS', 8),

    // separator for translations
    'gloss_translations_separator' => env('ED_GLOSS_TRANSLATIONS_SEPARATOR', ';').' ' /* deliberate trailing space */, 

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
        App\Models\Account::class         => App\Http\Discuss\Contexts\AccountContext::class,
        App\Models\Contribution::class    => App\Http\Discuss\Contexts\ContributionContext::class,
        App\Models\ForumDiscussion::class => App\Http\Discuss\Contexts\DiscussContext::class,
        App\Models\Sentence::class        => App\Http\Discuss\Contexts\SentenceContext::class,
        App\Models\Gloss::class           => App\Http\Discuss\Contexts\GlossContext::class
    ],
    'forum_resultset_max_length' => 10,
    'forum_thread_resultset_max_length' => 15
];
