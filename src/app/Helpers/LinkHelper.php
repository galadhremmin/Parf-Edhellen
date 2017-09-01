<?php

namespace App\Helpers;

use data\entities\AuthProvider;

class LinkHelper
{
    public function author(int $authorId, string $authorName)
    {
        $nickname = empty($authorName) ? '' : StringHelper::normalizeForUrl($authorName);
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

    public function translation(int $translationId)
    {
        return route('translation.ref', [
            'id' => $translationId
        ]);
    }

    public function translationVersions(int $translationId)
    {
        return route('translation.ref.version', [
            'id' => $translationId
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
}
