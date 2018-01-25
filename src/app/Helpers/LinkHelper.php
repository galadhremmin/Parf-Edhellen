<?php

namespace App\Helpers;

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
        int $sentenceFragmentId = 0)
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
            $url .= '#!'.$sentenceFragmentId;
        }

        return $url;
    }

    public function forumPost(int $postId, string $subject = '') 
    {
        if (empty($subject)) {
            return route('discuss.show', ['id' => $postId]);
        }

        throw new \Exception('Not implemented.');
    }

    public function mailCancellation(string $cancellationToken)
    {
        return route('mail-setting.cancellation', ['token' => $cancellationToken]);
    }

    public function contribution(int $contributionId)
    {
        return route('contribution.show', ['id' => $contributionId]);
    }
}
