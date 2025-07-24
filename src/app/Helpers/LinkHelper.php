<?php

namespace App\Helpers;

use App\Models\LexicalEntry;
use App\Models\Initialization\Morphs;
use App\Models\Sentence;

class LinkHelper
{
    public function author(int $authorId, ?string $authorName = null)
    {
        $nickname = $authorName === null || empty($authorName)
            ? ''
            : StringHelper::normalizeForUrl($authorName);

        if (empty($nickname)) {
            return route('author.profile-without-nickname', [
                'id' => $authorId,
            ]);
        }

        return route('author.profile', [
            'id' => $authorId,
            'nickname' => $nickname,
        ]);
    }

    public function lexicalEntry(int $lexicalEntryId)
    {
        return route('gloss.ref', [
            'id' => $lexicalEntryId,
        ]);
    }

    public function lexicalEntryVersions(int $lexicalEntryId)
    {
        return route('gloss.ref.version', [
            'id' => $lexicalEntryId,
        ]);
    }

    public function sentencesByLanguage(int $languageId, string $languageName)
    {
        $languageName = StringHelper::normalizeForUrl($languageName);

        return route('sentence.public.language', [
            'langId' => $languageId,
            'langName' => $languageName,
        ]);
    }

    public function sentence(int $languageId, string $languageName, int $sentenceId, string $sentenceName,
        int $sentenceSentenceId = 0, int $sentenceFragmentId = 0)
    {
        $languageName = StringHelper::normalizeForUrl($languageName);
        $sentenceName = StringHelper::normalizeForUrl($sentenceName);

        $url = route('sentence.public.sentence', [
            'langId' => $languageId,
            'langName' => $languageName,
            'sentId' => $sentenceId,
            'sentName' => $sentenceName,
        ]);

        if ($sentenceFragmentId !== 0) {
            $url .= '#!'.$sentenceSentenceId.'/'.$sentenceFragmentId;
        }

        return $url;
    }

    public function forumGroup(int $groupId, string $groupName)
    {
        return route('discuss.group', [
            'id' => $groupId,
            'slug' => StringHelper::normalizeForUrl($groupName),
        ]);
    }

    public function forumThread(int $groupId, string $groupName, int $threadId, ?string $normalizedSubject = null, $postId = 0)
    {
        $slug = $normalizedSubject === null || empty($normalizedSubject) ? 'thread' : $normalizedSubject;
        $props = [
            'id' => $threadId,
            'slug' => $slug,
            'groupId' => $groupId,
            'groupSlug' => StringHelper::normalizeForUrl($groupName),
        ];

        if ($postId !== 0) {
            $props['forum_post_id'] = $postId;
        }

        return route('discuss.show', $props);
    }

    public function resolveThreadByPost(int $postId)
    {
        return route('api.discuss.resolve-by-post', ['postId' => $postId]);
    }

    public function mailCancellation(string $cancellationToken)
    {
        return route('notifications.cancellation', ['token' => $cancellationToken]);
    }

    public function contribution(int $contributionId)
    {
        return route('contribution.show', ['contribution' => $contributionId]);
    }

    public function contributeGloss(int $originalGlossId = 0, int $lexicalEntryVersionId = 0)
    {
        $params = ['morph' => Morphs::getAlias(LexicalEntry::class)];
        if ($originalGlossId !== 0) {
            $params['entity_id'] = $originalGlossId;
        }
        if ($lexicalEntryVersionId !== 0) {
            $params['lexical_entry_version_id'] = $lexicalEntryVersionId;
        }

        return route('contribution.create', $params);
    }

    public function contributeSentence(int $sentenceId = 0)
    {
        $params = ['morph' => Morphs::getAlias(Sentence::class)];
        if ($sentenceId !== 0) {
            $params['entity_id'] = $sentenceId;
        }

        return route('contribution.create', $params);
    }
}
