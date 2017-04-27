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
    public function adaptFragments(Collection $fragments, $transformMarkdownToHtml = true)
    {
        $result = new Collection();
        $markdownParser = new MarkdownParser();

        foreach ($fragments as $fragment) {
            $data = [
                'id'               => $fragment->id,
                'fragment'         => $fragment->fragment,
                'tengwar'          => $fragment->tengwar,
                'interpunctuation' => $fragment->isPunctuationOrWhitespace(),
                'translation_id'   => $fragment->translation_id,
                'speech'           => $fragment->speech_id ? $fragment->speech->name : null,
                'comments'         => !empty($fragment->comments)
                    ? ($transformMarkdownToHtml ? $markdownParser->parse($fragment->comments) : $fragment->comments)
                    : null,
                'inflections'      => []
            ];

            // Todo: optimise this to reduce queries to the database and remove model awareness
            // as it's just dirty in this context!
            $inflections = $fragment->inflectionAssociations()
                ->join('inflections', 'inflections.id', 'inflection_id')
                ->where('sentence_fragment_id', $fragment->id)
                ->select('inflections.id', 'inflections.name')
                ->get();

            foreach ($inflections as $inflection) {
                $data['inflections'][] = $inflection;
            }

            $result->push($data);
        }

        return $result;
    }
}