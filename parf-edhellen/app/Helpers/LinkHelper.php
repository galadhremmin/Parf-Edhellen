<?php

namespace App\Helpers;

use data\entities\AuthProvider;

class LinkHelper
{
    public function author(int $authorId, string $authorName)
    {
        return route('author.profile', [
            'id' => $authorId,
            'nickname' => StringHelper::normalizeForUrl($authorName)
        ]);
    }

    public function translation(int $translationId)
    {
        return route('translation.ref', [
            'id' => $translationId
        ]);
    }
    
    public function authRedirect(string $url) {
        return route('auth.redirect', [
            'providerName' => $url
        ]);
    }
    
    public function phraseByLanguage(int $languageId, string $languageName) {
        $languageName = urlencode(strtolower($languageName));

        return route('phrases.language', [
            'langId'   => $languageId,
            'langName' => $languageName
        ]);
    }
}