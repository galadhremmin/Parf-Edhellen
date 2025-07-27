<?php

namespace App\Http\Controllers\Contributions;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\Contribution;
use App\Models\LexicalEntry;
use App\Models\LexicalEntryInflection;
use App\Models\Inflection;
use App\Models\Sentence;
use App\Models\Speech;
use App\Repositories\LexicalEntryInflectionRepository;
use App\Repositories\LexicalEntryRepository;
use Illuminate\Http\Request;

class GlossInflectionContributionController extends Controller implements IContributionController
{
    private LexicalEntryInflectionRepository $_lexicalEntryInflectionRepository;

    private LexicalEntryContributionController $_lexicalEntryContributionController;

    private LexicalEntryRepository $_lexicalEntryRepository;

    public function __construct(LexicalEntryInflectionRepository $lexicalEntryInflectionRepository,
        LexicalEntryRepository $lexicalEntryRepository, LexicalEntryContributionController $lexicalEntryContributionController)
    {
        $this->_lexicalEntryInflectionRepository = $lexicalEntryInflectionRepository;
        $this->_lexicalEntryContributionController = $lexicalEntryContributionController;
        $this->_lexicalEntryRepository = $lexicalEntryRepository;
    }

    public function getViewModel(Contribution $contribution): ViewModel
    {
        $payloadData = collect(json_decode($contribution->payload));

        $lexicalEntryInflections = $payloadData->map(function ($lexicalEntryInflection) {
            return (object) $lexicalEntryInflection;
        });

        $speeches = Speech::whereIn('id', $lexicalEntryInflections->pluck('speech_id')) //
            ->get()->keyBy('id');
        $inflections = Inflection::whereIn('id', $lexicalEntryInflections->pluck('inflection_id')) //
            ->get()->keyBy('id');

        // create a hierarchical structure that the UI expects based on the unique group identifier
        $lexicalEntryInflections = $lexicalEntryInflections->reduce(function ($carry, $i) use ($speeches, $inflections) {
            if (! isset($carry[$i->inflection_group_uuid])) {
                $i->inflections = [];
                $i->speech = $speeches[$i->speech_id]->name;
                $carry[$i->inflection_group_uuid] = $i;
            }

            $carry[$i->inflection_group_uuid]->inflections[] = $inflections[$i->inflection_id]->name;

            return $carry;
        }, []);

        $lexicalEntry = $contribution->lexical_entry_id ? LexicalEntry::find($contribution->lexical_entry_id) : null;
        $viewModel = [
            'review' => $contribution,
            'lexicalEntry' => $lexicalEntry,
            'inflections' => $lexicalEntryInflections,
        ];

        return new ViewModel($contribution, 'contribution.lex_entry_infl._show', $viewModel);
    }

    /**
     * HTTP GET. Opens a view for editing a lexical entry inflections contribution.
     *
     * @return array|\Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function edit(Contribution $contribution, Request $request)
    {
        $inflections = collect(json_decode($contribution->payload));
        $sentences = Sentence::whereIn('id', $inflections->pluck('sentence_id'))//
            ->get() //
            ->keyBy('id');
        $inflectionGroups = $inflections->map(function ($i) use ($sentences) {
            if ($i->sentence_id && $sentences->has($i->sentence_id)) {
                $i->sentence = $sentences[$i->sentence_id];
            }

            return $i;
        }) //
            ->groupBy('inflection_group_uuid');

        $lexicalEntryPayload = [
            'contribution_id' => $contribution->id,
            'id' => $contribution->dependent_on === null ? $contribution->gloss_id : 0,
        ];

        if ($lexicalEntryPayload['id']) {
            $lexicalEntry = $this->_lexicalEntryRepository->getLexicalEntry($lexicalEntryPayload['id']);
            if ($lexicalEntry->count() < 1) {
                throw new \Exception('Lexical entry '.$lexicalEntryPayload['id'].' does not exist.');
            }
            $lexicalEntryPayload = array_merge(
                $lexicalEntry->first()->toArray(),
                $lexicalEntryPayload
            );
        } else {
            $lexicalEntryPayload = array_merge(
                $this->_lexicalEntryContributionController->getEditViewModel($contribution->dependent_on),
                $lexicalEntryPayload
            );
        }

        $viewModel = [
            'payload' => $lexicalEntryPayload,
            'inflections' => $inflectionGroups,
            'form_restrictions' => ['inflections'],
            'review' => $contribution,
        ];

        return $request->ajax() //
            ? $viewModel : view('contribution.gloss.edit', $viewModel);
    }

    /**
     * Shows a form for a new contribution.
     *
     * @return array|\Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function create(Request $request)
    {
        throw new \Exception('Inflections are not independently supported.');
    }

    public function validateSubstep(Request $request, int $id = 0, int $substepId = 0): mixed
    {
        return true;
    }

    public function validateBeforeSave(Request $request, int $id = 0): bool
    {
        $request->validate([
            'lexical_entry_id' => 'sometimes|nullable|numeric|exists:lexical_entries,id',
            'inflection_groups' => 'required|array',
            'inflection_groups.*.inflection_group_uuid' => 'required|uuid',
            'inflection_groups.*.is_neologism' => 'sometimes|boolean',
            'inflection_groups.*.is_rejected' => 'sometimes|boolean',
            'inflection_groups.*.speech_id' => 'required|numeric|exists:speeches,id',
            'inflection_groups.*.sentence_fragment_id' => 'sometimes|numeric|nullable|exists:sentence_fragments,id',
            'inflection_groups.*.source' => 'sometimes|nullable|string|max:64',
            'inflection_groups.*.word' => 'required|string|max:196',

            'inflection_groups.*.inflections' => 'required|array',
            'inflection_groups.*.inflections.*.inflection_id' => 'required|numeric|exists:inflections,id',
        ]);

        return true;
    }

    public function populate(Contribution $contribution, Request $request)
    {
        $contribution->lexical_entry_id = $request->has('lexical_entry_id') //
            ? intval($request->input('lexical_entry_id')) : null;

        if ($contribution->lexical_entry_id) {
            $lexicalEntry = LexicalEntry::findOrFail($contribution->lexical_entry_id);
            $contribution->language_id = $lexicalEntry->language_id;
            $word = $lexicalEntry->word->word;
        } else {
            $dependency = Contribution::findOrFail($contribution->dependent_on_contribution_id);
            $contribution->language_id = $dependency->language_id;
            $word = $dependency->word;
        }

        // Word in this context is meaningless so be a little descriptive what this
        // contribution contains:
        $contribution->word = sprintf('Inflections of %s', $word);

        // The inflection_groups attribute as received from the client is hierarchical
        // where each group maintains its own list of inflections. The persistence layer
        // flattens the data structure and denormalizes for performance. This is what
        // the next couple of lines will do:
        $groups = $request->input('inflection_groups');

        return array_reduce($groups, function ($inflections, $group) {
            foreach ($group['inflections'] as $inflection) {
                $inflections[] = new LexicalEntryInflection(
                    array_merge($inflection, $group) // denormalization
                );
            }

            return $inflections;
        }, []);
    }

    /**
     * Disable change detection within the parent controller as the payload is always
     * populated by the `populate` method.
     */
    public function disableChangeDetection(): bool
    {
        return false;
    }

    public function approve(Contribution $contribution, Request $request): int
    {
        if ($contribution->dependent_on !== null) {
            $lexicalEntry = $contribution->dependent_on->entity;
        } else {
            $lexicalEntry = $contribution->lexical_entry;
        }

        $inflections = collect(json_decode($contribution->payload, /* associative? */ true))->map(function ($i) use ($lexicalEntry) {
            return new LexicalEntryInflection($i + [
                'lexical_entry_id' => $lexicalEntry->id,
                'language_id' => $lexicalEntry->language_id,
            ]);
        });

        $this->_lexicalEntryInflectionRepository->saveManyOnLexicalEntry($lexicalEntry, $inflections);

        return $lexicalEntry->id;
    }
}
