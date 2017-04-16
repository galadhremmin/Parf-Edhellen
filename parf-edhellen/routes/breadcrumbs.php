<?php


// Phrases (Home)
Breadcrumbs::register('sentences', function($breadcrumbs)
{
    $breadcrumbs->push('Home', route('sentences'));
});

// Phrases > [Language]
Breadcrumbs::register('sentences.language', function($breadcrumbs, int $langId, string $langName)
{
    $link = new \App\Helpers\LinkHelper();

    $breadcrumbs->parent('sentences');
    $breadcrumbs->push($langName, $link->sentenceByLanguage($langId, $langName));
});

// Phrases > [Language] > [Phrase]
Breadcrumbs::register('sentences.sentence', function($breadcrumbs, int $langId, string $langName,
                                                 int $sentenceId, string $sentenceName)
{
    $link = new \App\Helpers\LinkHelper();

    $breadcrumbs->parent('sentences.language', $langId, $langName);
    $breadcrumbs->push($sentenceName, $link->sentence($langId, $langName, $sentenceId, $sentenceName));
});