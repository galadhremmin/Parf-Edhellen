<?php
namespace App\Adapters;

use App\Helpers\MarkdownParser;
use App\Adapters\{ LatinSentenceBuilder, TengwarSentenceBuilder };
use Illuminate\Support\Collection;

class SentenceAdapter
{
    public function adaptFragments(Collection $fragmentRows, $transformMarkdownToHtml = true)
    {
        $fragments = [];
        $markdownParser = new MarkdownParser();

        foreach ($fragmentRows as $fragment) {
            $data = [
                'id'               => $fragment->id,
                'translation_id'   => $fragment->translation_id,
                'type'             => $fragment->type,
                'fragment'         => $fragment->fragment,
                'tengwar'          => $fragment->tengwar,
                'speech'           => $fragment->speech_id ? $fragment->speech->name : null,
                'speech_id'        => $fragment->speech_id,
                'comments'         => !empty($fragment->comments)
                    ? ($transformMarkdownToHtml ? $markdownParser->parse($fragment->comments) : $fragment->comments)
                    : null,
                'inflections'      => []
            ];

            // Todo: optimise this to reduce queries to the database and remove model awareness
            // as it's just dirty in this context!
            $inflections = $fragment->inflection_associations()
                ->join('inflections', 'inflections.id', 'inflection_id')
                ->where('sentence_fragment_id', $fragment->id)
                ->select('inflections.id', 'inflections.name')
                ->get();

            foreach ($inflections as $inflection) {
                $data['inflections'][] = $inflection;
            }

            $fragments[] = $data;
        }

        $latinBuilder = new LatinSentenceBuilder($fragments);
        $tengwarBuilder = new TengwarSentenceBuilder($fragments);
        return [
            'fragments' => $fragments,
            'latin'     => $latinBuilder->build(),
            'tengwar'   => $tengwarBuilder->build()
        ];
    }
}