<?php

/**
 * What the dictionary's own vocabulary means to sense normalisation: which parts of speech say what a sense *is*
 * rather than what it means, how they map onto WordNet's, and which concepts names belong to. Parts of speech are
 * rows an administrator can add, so these live here rather than in the classes that read them.
 */
return [
    // widen dictionary searches by sense headword (sense_terms), so "trees" finds "tree"
    'term_search' => env('ED_SENSE_TERM_SEARCH', true),

    // answer a search with the kinds of what was asked for as well: "trees" then finds the oaks and the alders
    'widening' => env('ED_SENSE_CONCEPT_WIDENING', true),

    // offer the kinds of what was searched for ("kinds of tree") alongside the results
    'concept_search' => env('ED_SENSE_CONCEPT_SEARCH', true),

    // how many kinds to send; the view shows the first few and reveals the rest without asking again
    'concept_search_limit' => env('ED_SENSE_CONCEPT_SEARCH_LIMIT', 40),

    // how far up to offer what the search is a kind of: birch, tree, woody plant, plant
    'broader_limit' => env('ED_SENSE_CONCEPT_BROADER_LIMIT', 4),

    // ask Gemini which meaning a contributed sense has; without it such a sense waits for an editor
    'judge' => env('ED_SENSE_CONCEPT_JUDGE', false),

    // a judge's answer below this confidence (0-100) goes to an editor instead of being assigned
    'minimum_confidence' => env('ED_SENSE_CONCEPT_MINIMUM_CONFIDENCE', 70),

    // how many of a result's concepts its kinds are drawn from
    'subject_concepts' => env('ED_SENSE_CONCEPT_SUBJECTS', 3),

    // above this many entries, a concept's kinds bury the results rather than widen them: "name" holds thousands
    'max_entries_under_subject' => env('ED_SENSE_CONCEPT_MAX_ENTRIES_UNDER_SUBJECT', 400),

    // walking up from birch, tree and plant are worth offering; organism and entity are where meaning runs out
    'max_entries_under_broader' => env('ED_SENSE_CONCEPT_MAX_ENTRIES_UNDER_BROADER', 1000),

    // a broad concept like "plant" has thousands below it, and counting entries for all of them costs a search
    'max_narrower_concepts' => env('ED_SENSE_CONCEPT_MAX_NARROWER', 400),

    // a prefix like "s" names thousands of concepts; only the most used of a shortlist are worth counting
    'suggestion_candidates' => env('ED_SENSE_CONCEPT_SUGGESTION_CANDIDATES', 60),

    // how many of a meaning's other words to show beside it
    'synonyms' => env('ED_SENSE_CONCEPT_SYNONYMS', 3),

    // how many meanings and wordings the form that writes a sense offers at a time
    'suggestions' => env('ED_SENSE_SUGGESTIONS', 8),

    // a sense whose entries are all named things: Gildir, Eglarest
    'name_speeches' => [
        'collective name', 'family name', 'feminine name', 'masculine name', 'name', 'place name', 'proper name',
    ],

    // a sense that describes grammar rather than meaning: "pronominal suffix", "superlative ending"
    'grammar_speeches' => ['affix', 'infix', 'phoneme', 'prefix', 'radical', 'suffix'],

    // English's closed classes, which carry no meaning a concept can hold
    'function_speeches' => [
        'article', 'auxillary verb', 'conjunction', 'definite article', 'interjection', 'interjection/preposition',
        'interrogative', 'particle', 'preposition', 'preposition and adverb', 'preposition/adverb',
        'preposition/conjunction', 'preposition/prefix', 'pronoun',
    ],

    // parts of speech that say nothing either way
    'unknown_speeches' => ['?'],

    // the WordNet parts of speech each one allows: n nouns, v verbs, a adjectives, s adjective satellites, r adverbs
    'wordnet_pos' => [
        'noun' => ['n'],
        'collective noun' => ['n'],
        'gerund noun' => ['n'],
        'adjective' => ['a', 's'],
        'adverb' => ['r'],
        'verb' => ['v'],
        'verb (impersonal)' => ['v'],
        'participle' => ['v', 'a', 's'],
        'noun/adjective' => ['n', 'a', 's'],
        'noun/adverb' => ['n', 'r'],
        'noun/verb' => ['n', 'v'],
        'adverb/adjective' => ['r', 'a', 's'],
    ],

    // the words English uses to hold a sentence together, for senses whose entries record no part of speech
    'function_words' => [
        'a', 'about', 'above', 'across', 'after', 'against', 'ah', 'alas', 'all', 'also', 'am', 'among', 'an', 'and',
        'another', 'any', 'are', 'around', 'as', 'at', 'be', 'because', 'been', 'before', 'behind', 'being', 'below',
        'beneath', 'beside', 'besides', 'between', 'beyond', 'both', 'but', 'by', 'can', 'could', 'did', 'do', 'does',
        'during', 'each', 'either', 'every', 'for', 'from', 'had', 'has', 'have', 'he', 'hence', 'her', 'here', 'hers',
        'herself', 'him', 'himself', 'his', 'hither', 'i', 'if', 'in', 'into', 'is', 'it', 'its', 'itself', 'lo', 'may',
        'me', 'might', 'mine', 'must', 'my', 'myself', 'neither', 'no', 'nor', 'not', 'o', 'of', 'off', 'oh', 'on',
        'onto', 'or', 'our', 'ours', 'ourselves', 'she', 'shall', 'should', 'since', 'so', 'some', 'than', 'that', 'the',
        'their', 'theirs', 'them', 'themselves', 'then', 'thence', 'there', 'these', 'they', 'this', 'thither', 'those',
        'though', 'through', 'thus', 'to', 'toward', 'towards', 'unless', 'until', 'unto', 'upon', 'us', 'was', 'we',
        'were', 'what', 'whatever', 'whence', 'when', 'where', 'whether', 'which', 'while', 'whither', 'who', 'whom',
        'whose', 'why', 'will', 'with', 'within', 'without', 'would', 'ye', 'yes', 'yet', 'you', 'your', 'yours',
        'yourself', 'yourselves',
    ],

    // the WordNet meaning each kind of name belongs to; WordNet files them all under "name"
    'name_concepts' => [
        'masculine name' => '06348677-n',
        'feminine name' => '06348677-n',
        'family name' => '06348274-n',
        'place name' => '06355208-n',
        'collective name' => '06344646-n',
        'proper name' => '06344646-n',
        'name' => '06344646-n',
    ],

    // what a name of no stated kind belongs to
    'default_name_concept' => '06344646-n',
];
