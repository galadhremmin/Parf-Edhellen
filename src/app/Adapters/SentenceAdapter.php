<?php
/**
 * Created by PhpStorm.
 * User: zanathel
 * Date: 4/16/17
 * Time: 6:06 PM
 */

namespace App\Adapters;


use App\Helpers\MarkdownParser;
use Illuminate\Support\Collection;

class SentenceAdapter
{
    public function adaptFragments(Collection $fragments)
    {
        $result = new Collection();
        $markdownParser = new MarkdownParser();

        foreach ($fragments as $fragment) {
            $result->push([
                'id'               => $fragment->FragmentID,
                'fragment'         => $fragment->Fragment,
                'tengwar'          => $fragment->Tengwar,
                'interpunctuation' => $fragment->isPunctuationOrWhitespace(),
                'translationId'    => $fragment->TranslationID,
                'grammarType'      => $fragment->GrammarTypeID ? $fragment->grammarType->Name : null,
                'comments'         => !empty($fragment->Comments)
                    ? $markdownParser->parse($fragment->Comments)
                    : null
            ]);
        }

        return $result;
    }
}