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
        'latin'   => App\Helpers\SentenceBuilders\LatinSentenceBuilder::class,
        'tengwar' => App\Helpers\SentenceBuilders\TengwarSentenceBuilder::class
    ],

    // Sitemap for unlocking sitemap view 
    'sitemap_key' => env('ED_SITEMAP_KEY', ''),

    // Logging to database?
    'system_errors_logging' => env('ED_SYSTEM_ERRORS_LOGGING', false),

    'forum_entities' => [
        App\Models\Account::class                 => App\Http\Discuss\Contexts\AccountContext::class,
        App\Models\Contribution::class            => App\Http\Discuss\Contexts\ContributionContext::class,
        App\Models\ForumDiscussion::class         => App\Http\Discuss\Contexts\DiscussContext::class,
        App\Models\Sentence::class                => App\Http\Discuss\Contexts\SentenceContext::class,
        App\Models\Versioning\GlossVersion::class => App\Http\Discuss\Contexts\GlossVersionContext::class
    ],
    'forum_resultset_max_length' => 10,
    'forum_thread_resultset_max_length' => 15,
    'forum_pagination_max_pages' => 6,
    'forum_pagination_first_page_number' => 1, // This should really never have to change.

    // book configuration, including resolvers.
    // NOTE: when adding new entities, ensure that the following files are available:
    //       1. resources/views/book/<morph alias>/index.blade.php
    //       2. resources/assets/ts/apps/book-browser/<frontend alias>/index.ts.
    'book_entities' => [
        App\Models\Gloss::class => [
            'group_id'            => App\Models\SearchKeyword::SEARCH_GROUP_DICTIONARY,
            'resolver'            => App\Repositories\SearchIndexResolvers\GlossSearchIndexResolver::class,
            'intl_name'           => 'glossary',
            'discuss_entity_type' => App\Models\Versioning\GlossVersion::class
        ],
        App\Models\ForumPost::class => [
            'group_id'            => App\Models\SearchKeyword::SEARCH_GROUP_FORUM_POST,
            'resolver'            => App\Repositories\SearchIndexResolvers\ForumPostSearchIndexResolver::class,
            'intl_name'           => 'discuss',
            'discuss_entity_type' => null // defaults to entity type
        ],
        App\Models\SentenceFragment::class => [
            'group_id'           => App\Models\SearchKeyword::SEARCH_GROUP_SENTENCE,
            'resolver'           => App\Repositories\SearchIndexResolvers\SentenceSearchIndexResolver::class,
            'intl_name'          => 'sentence',
            'discuss_entity_type' => null // default to entity type
        ]
    ],
    'book_group_id_to_book_entities' => [
        // This is just a fast lookup table used by the search index repository. It should mirror the `book_entities` configuration
        // The entirety of the search index must be rebuilt if you change the order of these entries!
        App\Models\SearchKeyword::SEARCH_GROUP_DICTIONARY => App\Models\Gloss::class,
        App\Models\SearchKeyword::SEARCH_GROUP_FORUM_POST => App\Models\ForumPost::class,
        App\Models\SearchKeyword::SEARCH_GROUP_SENTENCE   => App\Models\SentenceFragment::class
    ],

    // sentence repository configuration
    'sentence_repository_maximum_fragments' => 100,
    
    // gloss repository configuration
    'gloss_repository_maximum_results' => 1000
];
