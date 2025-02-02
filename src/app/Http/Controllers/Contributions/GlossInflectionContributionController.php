<?php

namespace App\Http\Controllers\Contributions;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\Contribution;
use App\Models\Gloss;
use App\Models\GlossInflection;
use App\Models\Inflection;
use App\Models\Sentence;
use App\Models\Speech;
use App\Repositories\GlossInflectionRepository;
use App\Repositories\GlossRepository;
use Illuminate\Http\Request;

class GlossInflectionContributionController extends Controller implements IContributionController
{
    private GlossInflectionRepository $_glossInflectionRepository;

    private GlossContributionController $_glossContributionController;

    private GlossRepository $_glossRepository;

    public function __construct(GlossInflectionRepository $glossInflectionRepository,
        GlossRepository $glossRepository, GlossContributionController $glossContributionController)
    {
        $this->_glossInflectionRepository = $glossInflectionRepository;
        $this->_glossContributionController = $glossContributionController;
        $this->_glossRepository = $glossRepository;
    }

    public function getViewModel(Contribution $contribution): ViewModel
    {
        $payloadData = collect(json_decode($contribution->payload));

        $glossInflections = $payloadData->map(function ($glossInflection) {
            return (object) $glossInflection;
        });

        $speeches = Speech::whereIn('id', $glossInflections->pluck('speech_id')) //
            ->get()->keyBy('id');
        $inflections = Inflection::whereIn('id', $glossInflections->pluck('inflection_id')) //
            ->get()->keyBy('id');

        // create a hierarchical structure that the UI expects based on the unique group identifier
        $glossInflections = $glossInflections->reduce(function ($carry, $i) use ($speeches, $inflections) {
            if (! isset($carry[$i->inflection_group_uuid])) {
                $i->inflections = [];
                $i->speech = $speeches[$i->speech_id]->name;
                $carry[$i->inflection_group_uuid] = $i;
            }

            $carry[$i->inflection_group_uuid]->inflections[] = $inflections[$i->inflection_id]->name;

            return $carry;
        }, []);

        $gloss = $contribution->gloss_id ? Gloss::find($contribution->gloss_id) : null;
        $viewModel = [
            'review' => $contribution,
            'gloss' => $gloss,
            'inflections' => $glossInflections,
        ];

        return new ViewModel($contribution, 'contribution.gloss_infl._show', $viewModel);
    }

    /**
     * HTTP GET. Opens a view for editing a gloss inflections contribution.
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

        $glossPayload = [
            'contribution_id' => $contribution->id,
            'id' => $contribution->dependent_on === null ? $contribution->gloss_id : 0,
        ];

        if ($glossPayload['id']) {
            $gloss = $this->_glossRepository->getGloss($glossPayload['id']);
            if ($gloss->count() < 1) {
                throw new \Exception('Gloss '.$glossPayload['id'].' does not exist.');
            }
            $glossPayload = array_merge(
                $gloss->first()->toArray(),
                $glossPayload
            );
        } else {
            $glossPayload = array_merge(
                $this->_glossContributionController->getEditViewModel($contribution->dependent_on),
                $glossPayload
            );
        }

        $viewModel = [
            'payload' => $glossPayload,
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

    public function validateSubstep(Request $request, int $id = 0, int $substepId = 0): bool
    {
        return true;
    }

    public function validateBeforeSave(Request $request, int $id = 0): bool
    {
        $request->validate([
            'gloss_id' => 'sometimes|nullable|numeric|exists:glosses,id',
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
        $contribution->gloss_id = $request->has('gloss_id') //
            ? intval($request->input('gloss_id')) : null;

        if ($contribution->gloss_id) {
            $gloss = Gloss::findOrFail($contribution->gloss_id);
            $contribution->language_id = $gloss->language_id;
            $word = $gloss->word->word;
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
                $inflections[] = new GlossInflection(
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
            $gloss = $contribution->dependent_on->entity;
        } else {
            $gloss = $contribution->gloss;
        }

        $inflections = collect(json_decode($contribution->payload, /* associative? */ true))->map(function ($i) use ($gloss) {
            return new GlossInflection($i + [
                'gloss_id' => $gloss->id,
                'language_id' => $gloss->language_id,
            ]);
        });

        $this->_glossInflectionRepository->saveManyOnGloss($gloss, $inflections);

        return $gloss->id;
    }
}
