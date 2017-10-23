<?php

// Home
Breadcrumbs::register('home', function($breadcrumbs)
{
    $breadcrumbs->push('Home', route('home'));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Phrases

Breadcrumbs::register('sentence.public', function($breadcrumbs)
{
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Phrases', route('sentence.public'));
});

// Phrases > [Language]
Breadcrumbs::register('sentence.public.language', function($breadcrumbs, int $langId, string $langName)
{
    $link = new \App\Helpers\LinkHelper();

    $breadcrumbs->parent('sentence.public');
    $breadcrumbs->push($langName, $link->sentencesByLanguage($langId, $langName));
});

// Phrases > [Language] > [Phrase]
Breadcrumbs::register('sentence.public.sentence', function($breadcrumbs, int $langId, string $langName,
                                                 int $sentenceId, string $sentenceName)
{
    $link = new \App\Helpers\LinkHelper();

    $breadcrumbs->parent('sentence.public.language', $langId, $langName);
    $breadcrumbs->push($sentenceName, $link->sentence($langId, $langName, $sentenceId, $sentenceName));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Dashboard

Breadcrumbs::register('dashboard', function ($breadcrumbs)
{
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Dashboard', route('dashboard'));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Dashboard > Speech

Breadcrumbs::register('speech.index', function ($breadcrumbs)
{
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('Type of speeches', route('speech.index'));
});

// Dashboard > Speech > Add speech
Breadcrumbs::register('speech.create', function ($breadcrumbs)
{
    $breadcrumbs->parent('speech.index');
    $breadcrumbs->push('Add type of speech', route('speech.create'));
});

// Dashboard > Speech > [Speech name]
Breadcrumbs::register('speech.edit', function ($breadcrumbs, App\Models\Speech $speech)
{
    $breadcrumbs->parent('speech.index');
    $breadcrumbs->push('Speech: '.$speech->Name, route('speech.edit', [ 'id' => $speech->SpeechID ]));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Dashboard > Inflections

Breadcrumbs::register('inflection.index', function ($breadcrumbs)
{
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('Inflections', route('inflection.index'));
});

// Dashboard > Inflections > Add inflection
Breadcrumbs::register('inflection.create', function ($breadcrumbs)
{
    $breadcrumbs->parent('inflection.index');
    $breadcrumbs->push('Add inflection', route('inflection.create'));
});

// Dashboard > Inflections > Edit [Inflection]
Breadcrumbs::register('inflection.edit', function ($breadcrumbs, App\Models\Inflection $inflection)
{
    $breadcrumbs->parent('inflection.index');
    $breadcrumbs->push('Inflection: '.$inflection->Name, route('inflection.edit', [
        'id' => $inflection->InflectionID
    ]));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Dashboard > Phrases

Breadcrumbs::register('sentence.index', function ($breadcrumbs)
{
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('Phrases', route('sentence.index'));
});

Breadcrumbs::register('sentence.create', function ($breadcrumbs)
{
    $breadcrumbs->parent('sentence.index');
    $breadcrumbs->push('Add phrase', route('sentence.create'));
});

Breadcrumbs::register('sentence.edit', function ($breadcrumbs, App\Models\Sentence $sentence)
{
    $breadcrumbs->parent('sentence.index');
    $breadcrumbs->push('Edit phrase (' . $sentence->name . ')', route('sentence.edit', [
        'id' => $sentence->id
    ]));
});

Breadcrumbs::register('sentence.confirm-destroy', function ($breadcrumbs, App\Models\Sentence $sentence)
{
    $breadcrumbs->parent('sentence.index');
    $breadcrumbs->push('Delete phrase (' . $sentence->name . ')', route('sentence.confirm-destroy', [
        'id' => $sentence->id
    ]));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Dashboard > Glossary

Breadcrumbs::register('translation.index', function ($breadcrumbs)
{
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('Glossary', route('translation.index'));
});

Breadcrumbs::register('translation.create', function ($breadcrumbs)
{
    $breadcrumbs->parent('translation.index');
    $breadcrumbs->push('Add gloss', route('translation.create'));
});

Breadcrumbs::register('translation.edit', function ($breadcrumbs, App\Models\Translation $translation)
{
    $breadcrumbs->parent('translation.index');
    $breadcrumbs->push('Edit gloss (' . $translation->word->word . ')', route('translation.edit', [
        'id' => $translation->id
    ]));
});

Breadcrumbs::register('translation.list', function ($breadcrumbs, App\Models\Language $language)
{
    $breadcrumbs->parent('translation.index');
    $breadcrumbs->push('Glossary for ' . $language->name, route('translation.list', [
        'id' => $language->id
    ]));
});

Breadcrumbs::register('translation.confirm-delete', function ($breadcrumbs, App\Models\Translation $translation)
{
    $breadcrumbs->parent('translation.index');
    $breadcrumbs->push('Delete gloss ' . $translation->word->word);
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Dashboard > Contributions
Breadcrumbs::register('contribution.index', function ($breadcrumbs)
{
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('Contributions', route('contribution.index'));
});

Breadcrumbs::register('contribution.create', function ($breadcrumbs, string $morph)
{
    $breadcrumbs->parent('contribution.index');
    $breadcrumbs->push('Contribute gloss', route('contribution.create', ['morph' => $morph]));
});

Breadcrumbs::register('contribution.edit', function ($breadcrumbs, int $id)
{
    $breadcrumbs->parent('contribution.index');
    $breadcrumbs->push('Change contribution', route('contribution.edit', ['id' => $id]));
});

Breadcrumbs::register('contribution.show', function ($breadcrumbs, int $id)
{
    $breadcrumbs->parent('contribution.index');
    $breadcrumbs->push('Contribution #'.$id, route('contribution.show', ['id' => $id]));
});

Breadcrumbs::register('contribution.list', function ($breadcrumbs)
{
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('Administration of contributions', route('contribution.list'));
});

Breadcrumbs::register('contribution.confirm-destroy', function ($breadcrumbs, int $id)
{
    $breadcrumbs->parent('contribution.show', $id);
    $breadcrumbs->push('Confirm deletion', route('contribution.confirm-destroy', ['id' => $id]));
});

Breadcrumbs::register('contribution.confirm-reject', function ($breadcrumbs, int $id)
{
    $breadcrumbs->parent('contribution.show', $id);
    $breadcrumbs->push('Confirm rejection', route('contribution.confirm-reject', ['id' => $id]));
});

Breadcrumbs::register('contribution.confirm-approve', function ($breadcrumbs, int $id)
{
    $breadcrumbs->parent('contribution.show', $id);
    $breadcrumbs->push('Approved!', route('contribution.confirm-approve', ['id' => $id]));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Dashboard > Flashcards

Breadcrumbs::register('flashcard', function ($breadcrumbs)
{
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('Flashcards', route('flashcard'));
});

Breadcrumbs::register('flashcard.cards', function ($breadcrumbs, App\Models\Flashcard $flashcard)
{
    $breadcrumbs->parent('flashcard');
    $breadcrumbs->push('Flashcard for '.$flashcard->language->name, route('flashcard.cards', ['id' => $flashcard->id]));
});

Breadcrumbs::register('flashcard.list', function ($breadcrumbs, App\Models\Flashcard $flashcard)
{
    $breadcrumbs->parent('flashcard');
    $breadcrumbs->push('Results for '.$flashcard->language->name, route('flashcard.list', ['id' => $flashcard->language->id]));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Forum 

Breadcrumbs::register('discuss', function ($breadcrumbs)
{
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Discussion', route('discuss.index'));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Dashboard > System errors

Breadcrumbs::register('system-error.index', function ($breadcrumbs)
{
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('List system errors', route('system-error.index'));
});
