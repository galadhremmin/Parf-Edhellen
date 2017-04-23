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
                'id'               => $fragment->id,
                'fragment'         => $fragment->fragment,
                'tengwar'          => $fragment->tengwar,
                'interpunctuation' => $fragment->isPunctuationOrWhitespace(),
                'translationId'    => $fragment->translation_id,
                'speech'           => $fragment->speech_id ? $fragment->speech->name : null,
                'comments'         => !empty($fragment->comments)
                    ? $markdownParser->parse($fragment->comments)
                    : null
            ]);
        }

        return $result;
    }
}