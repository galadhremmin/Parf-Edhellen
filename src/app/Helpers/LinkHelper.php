<?php

namespace App\Helpers;

use App\Models\{
    Gloss,
    Sentence
};
use App\Models\Initialization\Morphs;

class LinkHelper
{
    public function author(int $authorId, string $authorName = null)
    {
        $nickname = $authorName === null || empty($authorName) 
            ? '' 
            : StringHelper::normalizeForUrl($authorName);
        
        if (empty($nickname)) {
            return route('author.profile-without-nickname', [
                'id' => $authorId
            ]);
        }

        return route('author.profile', [
            'id' => $authorId,
            'nickname' => $nickname
        ]);
    }

    public function gloss(int $glossId)
    {
        return route('gloss.ref', [
            'id' => $glossId
        ]);
    }

    public function glossVersions(int $glossId)
    {
        return route('gloss.ref.version', [
            'id' => $glossId
        ]);
    }
    
    public function authRedirect(string $url)
    {
        return route('auth.redirect', [
            'providerName' => $url
        ]);
    }
    
    public function sentencesByLanguage(int $languageId, string $languageName)
    {
        $languageName = StringHelper::normalizeForUrl($languageName);

        return route('sentence.public.language', [
            'langId'   => $languageId,
            'langName' => $languageName
        ]);
    }

    public function sentence(int $languageId, string $languageName, int $sentenceId, string $sentenceName,
        int $sentenceSentenceId = 0, int $sentenceFragmentId = 0)
    {
        $languageName = StringHelper::normalizeForUrl($languageName);
        $sentenceName = StringHelper::normalizeForUrl($sentenceName);

        $url = route('sentence.public.sentence', [
            'langId'   => $languageId,
            'langName' => $languageName,
            'sentId'   => $sentenceId,
            'sentName' => $sentenceName
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
            'slug' => StringHelper::normalizeForUrl($groupName)
        ]);
    }

    public function forumThread(int $groupId, string $groupName, int $threadId, string $normalizedSubject = null, $postId = 0)
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
        return route('discuss.resolve-by-post', [ 'postId' => $postId ]);
    }

    public function mailCancellation(string $cancellationToken)
    {
        return route('mail-setting.cancellation', ['token' => $cancellationToken]);
    }

    public function contribution(int $contributionId)
    {
        return route('contribution.show', ['contribution' => $contributionId]);
    }

    public function contributeGloss(int $originalGlossId = 0)
    {
        $params = ['morph' => Morphs::getAlias(Gloss::class)];
        if ($originalGlossId !== 0) {
            $params['entity_id'] = $originalGlossId;
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
