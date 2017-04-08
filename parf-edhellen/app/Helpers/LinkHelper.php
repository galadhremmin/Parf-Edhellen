<?php

namespace App\Helpers;

class LinkHelper
{
    public function author($authorId, $authorName)
    {
        return route('author.profile', [
            'id' => $authorId,
            'nickname' => StringHelper::normalizeForUrl($authorName)
        ]);
    }

    public function translation($translationId)
    {
        return route('translation.ref', [
            'id' => $translationId
        ]);
    }
}