<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

// Home

Breadcrumbs::for('home', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->push('Home', route('home'));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Phrases

Breadcrumbs::for('sentence.public', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Phrases', route('sentence.public'));
});

// Phrases > [Language]
Breadcrumbs::for('sentence.public.language', function (BreadcrumbTrail $breadcrumbs, int $langId, string $langName) {
    $link = new \App\Helpers\LinkHelper;

    $breadcrumbs->parent('sentence.public');
    $breadcrumbs->push($langName, $link->sentencesByLanguage($langId, $langName));
});

// Phrases > [Language] > [Phrase]
Breadcrumbs::for('sentence.public.sentence', function (BreadcrumbTrail $breadcrumbs, int $langId, string $langName,
    int $sentenceId, string $sentenceName) {
    $link = new \App\Helpers\LinkHelper;

    $breadcrumbs->parent('sentence.public.language', $langId, $langName);
    $breadcrumbs->push($sentenceName, $link->sentence($langId, $langName, $sentenceId, $sentenceName));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Dashboard

Breadcrumbs::for('dashboard', function (BreadcrumbTrail $breadcrumbs) {
    $name = request()->user()->nickname;

    $breadcrumbs->parent('home');
    $breadcrumbs->push($name, route('author.my-profile'));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Dashboard > Speech

Breadcrumbs::for('speech.index', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('Type of speeches', route('speech.index'));
});

// Dashboard > Speech > Add speech
Breadcrumbs::for('speech.create', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('speech.index');
    $breadcrumbs->push('Add type of speech', route('speech.create'));
});

// Dashboard > Speech > [Speech name]
Breadcrumbs::for('speech.edit', function (BreadcrumbTrail $breadcrumbs, App\Models\Speech $speech) {
    $breadcrumbs->parent('speech.index');
    $breadcrumbs->push('Speech: '.$speech->name, route('speech.edit', ['speech' => $speech->id]));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Dashboard > Inflections

Breadcrumbs::for('inflection.index', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('Inflections', route('inflection.index'));
});

// Dashboard > Inflections > Add inflection
Breadcrumbs::for('inflection.create', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('inflection.index');
    $breadcrumbs->push('Add inflection', route('inflection.create'));
});

// Dashboard > Inflections > Edit [Inflection]
Breadcrumbs::for('inflection.edit', function (BreadcrumbTrail $breadcrumbs, App\Models\Inflection $inflection) {
    $breadcrumbs->parent('inflection.index');
    $breadcrumbs->push('Inflection: '.$inflection->name, route('inflection.edit', [
        'inflection' => $inflection->id,
    ]));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Dashboard > Phrases

Breadcrumbs::for('sentence.index', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('Phrases', route('sentence.index'));
});

Breadcrumbs::for('sentence.create', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('sentence.index');
    $breadcrumbs->push('Add phrase', route('sentence.create'));
});

Breadcrumbs::for('sentence.edit', function (BreadcrumbTrail $breadcrumbs, App\Models\Sentence $sentence) {
    $breadcrumbs->parent('sentence.index');
    $breadcrumbs->push('Edit phrase ('.$sentence->name.')', route('sentence.edit', [
        'inflection' => $sentence->id,
    ]));
});

Breadcrumbs::for('sentence.confirm-destroy', function (BreadcrumbTrail $breadcrumbs, App\Models\Sentence $sentence) {
    $breadcrumbs->parent('sentence.index');
    $breadcrumbs->push('Delete phrase ('.$sentence->name.')', route('sentence.confirm-destroy', [
        'id' => $sentence->id,
    ]));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Dashboard > Glossary

Breadcrumbs::for('gloss.index', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('Glossary', route('gloss.index'));
});

Breadcrumbs::for('gloss.create', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('gloss.index');
    $breadcrumbs->push('Add gloss', route('gloss.create'));
});

Breadcrumbs::for('gloss.edit', function (BreadcrumbTrail $breadcrumbs, App\Models\LexicalEntry $lexicalEntry) {
    $breadcrumbs->parent('gloss.index');
    $breadcrumbs->push('Edit gloss ('.$lexicalEntry->word->word.')', route('gloss.edit', [
        'id' => $lexicalEntry->id,
    ]));
});

Breadcrumbs::for('gloss.list', function (BreadcrumbTrail $breadcrumbs, App\Models\Language $language) {
    $breadcrumbs->parent('gloss.index');
    $breadcrumbs->push('Glossary for '.$language->name, route('gloss.list', [
        'id' => $language->id,
    ]));
});

Breadcrumbs::for('gloss.confirm-delete', function (BreadcrumbTrail $breadcrumbs, App\Models\LexicalEntry $lexicalEntry) {
    $breadcrumbs->parent('gloss.index');
    $breadcrumbs->push('Delete gloss '.$lexicalEntry->word->word);
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Dashboard > Contributions
Breadcrumbs::for('contribution.index', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('Contributions', route('contribution.index'));
});

Breadcrumbs::for('contribution.create', function (BreadcrumbTrail $breadcrumbs, string $morph) {
    $breadcrumbs->parent('contribution.index');
    $breadcrumbs->push('Contribute gloss', route('contribution.create', ['morph' => $morph]));
});

Breadcrumbs::for('contribution.edit', function (BreadcrumbTrail $breadcrumbs, int $id) {
    $breadcrumbs->parent('contribution.index');
    $breadcrumbs->push('Change contribution', route('contribution.edit', ['contribution' => $id]));
});

Breadcrumbs::for('contribution.show', function (BreadcrumbTrail $breadcrumbs, int $id, bool $admin = false) {
    $breadcrumbs->parent($admin ? 'contribution.list' : 'contribution.index');
    $breadcrumbs->push('Contribution #'.$id, route('contribution.show', ['contribution' => $id]));
});

Breadcrumbs::for('contribution.list', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('Administration of contributions', route('contribution.list'));
});

Breadcrumbs::for('contribution.confirm-destroy', function (BreadcrumbTrail $breadcrumbs, int $id) {
    $breadcrumbs->parent('contribution.show', $id);
    $breadcrumbs->push('Confirm deletion', route('contribution.confirm-destroy', ['id' => $id]));
});

Breadcrumbs::for('contribution.confirm-reject', function (BreadcrumbTrail $breadcrumbs, int $id) {
    $breadcrumbs->parent('contribution.show', $id);
    $breadcrumbs->push('Confirm rejection', route('contribution.confirm-reject', ['id' => $id]));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Games

Breadcrumbs::for('games', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Games', route('games'));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Games > Flashcards

Breadcrumbs::for('flashcard', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('games');
    $breadcrumbs->push('Flashcards', route('flashcard'));
});

Breadcrumbs::for('flashcard.cards', function (BreadcrumbTrail $breadcrumbs, App\Models\Flashcard $flashcard) {
    $breadcrumbs->parent('flashcard');
    $breadcrumbs->push('Flashcard for '.$flashcard->language->name, route('flashcard.cards', ['id' => $flashcard->id]));
});

Breadcrumbs::for('flashcard.list', function (BreadcrumbTrail $breadcrumbs, App\Models\Flashcard $flashcard) {
    $breadcrumbs->parent('flashcard');
    $breadcrumbs->push('Results for '.$flashcard->language->name, route('flashcard.list', ['id' => $flashcard->language->id]));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Games > Sage

Breadcrumbs::for('word-finder', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('games');
    $breadcrumbs->push('Sage', route('word-finder.index'));
});

Breadcrumbs::for('word-finder.show', function (BreadcrumbTrail $breadcrumbs, App\Models\GameWordFinderLanguage $game) {
    $breadcrumbs->parent('word-finder');
    $breadcrumbs->push($game->language->name.' Sage', route('word-finder.show', ['languageId' => $game->language_id]));
});

Breadcrumbs::for('word-finder.config.index', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('word-finder');
    $breadcrumbs->push(__('word-finder.config.title'), route('word-finder.config.index'));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Forum

Breadcrumbs::for('discuss', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Discussion', route('discuss.index'));
});

Breadcrumbs::for('discuss.group', function (BreadcrumbTrail $breadcrumbs, App\Models\ForumGroup $group) {
    $breadcrumbs->parent('discuss');

    $linker = new \App\Helpers\LinkHelper;
    $breadcrumbs->push($group->name, $linker->forumGroup($group->id, $group->name));
});

Breadcrumbs::for('discuss.show', function (BreadcrumbTrail $breadcrumbs, App\Models\ForumGroup $group, App\Models\ForumThread $thread) {
    $breadcrumbs->parent('discuss.group', $group);

    $linker = new \App\Helpers\LinkHelper;
    $breadcrumbs->push($thread->subject, $linker->forumThread($group->id, $group->name, $thread->id, $thread->normalized_subject));
});

Breadcrumbs::for('discuss.create', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('discuss');
    $breadcrumbs->push('New thread', route('discuss.create'));
});

Breadcrumbs::for('discuss.members', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('discuss');
    $breadcrumbs->push('Contributors', route('discuss.members'));
});

Breadcrumbs::for('discuss.member-list', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('discuss.members');
    $breadcrumbs->push('All contributors', route('discuss.member-list'));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Dashboard > System errors

Breadcrumbs::for('system-error.index', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('List system errors', route('system-error.index'));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Dashboard > Accounts

Breadcrumbs::for('account.index', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('Accounts', route('account.index'));
});

Breadcrumbs::for('account.edit', function (BreadcrumbTrail $breadcrumbs, App\Models\Account $account) {
    $breadcrumbs->parent('account.index');
    $breadcrumbs->push('Account '.$account->nickname.' ('.$account->id.')', route('account.edit', ['account' => $account->id]));
});

Breadcrumbs::for('account.by-role', function (BreadcrumbTrail $breadcrumbs, App\Models\Role $role) {
    $breadcrumbs->parent('account.index');
    $breadcrumbs->push('Accounts in '.$role->name, route('account.by-role', ['id' => $role->id]));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Dashboard > Notification settings
Breadcrumbs::for('notifications.index', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('Notification settings', route('notifications.index'));
});

// //////////////////////////////////////////////////////////////////////////////////////////////
// Dashboard > Security

Breadcrumbs::for('account.security', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('dashboard');
    $breadcrumbs->push('Security', route('account.security'));
});

Breadcrumbs::for('account.merge-status', function (BreadcrumbTrail $breadcrumbs, App\Models\AccountMergeRequest $mergeRequest) {
    $breadcrumbs->parent('account.security');
    $breadcrumbs->push('Request '.$mergeRequest->id.' ('.$mergeRequest->created_at.')', route('account.merge-status', ['requestId' => $mergeRequest->id]));
});

Breadcrumbs::for('verification.notice', function (BreadcrumbTrail $breadcrumbs) {
    $breadcrumbs->parent('account.security');
    $breadcrumbs->push('E-mail verification', route('verification.notice'));
});
