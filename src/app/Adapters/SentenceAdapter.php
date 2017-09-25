<?php
namespace App\Adapters;

use App\Helpers\MarkdownParser;
use App\Adapters\{ LatinSentenceBuilder, TengwarSentenceBuilder };
use App\Models\{
    ModelBase, 
    Inflection
};
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
            $query = null;
            
            if ($fragment instanceof ModelBase && $fragment->hasAttribute('_inflections')) {
                $query = Inflection::whereIn('id', $fragment->_inflections);
            } else {
                $query = $fragment->inflection_associations()
                    ->join('inflections', 'inflections.id', 'inflection_id')
                    ->where('sentence_fragment_id', $fragment->id);
            }

            $inflections = $query->select('inflections.id', 'inflections.name')->get();
            foreach ($inflections as $inflection) {
                $data['inflections'][] = $inflection;
            }

            $fragments[] = $data;
        }

        $result = [
            'fragments' => $fragments
        ];

        $sentences = $this->adaptFragmentsToSentences($fragments);
        foreach ($sentences as $name => $paragraphs) {
            $result[$name] = $paragraphs;
        }

        return $result;
    }

    public function adaptFragmentsToSentences(array $adaptedFragments, string $builderName = null) {
        $result = [];

        $sentenceBuilders = config('ed.required_sentence_builders');
        foreach ($sentenceBuilders as $name => $class) {
            if ($builderName !== null && $name !== $builderName) {
                continue;
            }

            $builder = new $class($adaptedFragments);
            $result[$name] = $builder->build();
        }

        return $result;
    }
}
