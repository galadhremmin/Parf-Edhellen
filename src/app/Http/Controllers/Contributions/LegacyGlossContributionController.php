<?php

namespace App\Http\Controllers\Contributions;

use App\Models\Contribution;
use App\Models\LexicalEntryDetail;
use App\Models\Gloss;
use Illuminate\Http\Request;

class LegacyGlossContributionController extends LexicalEntryContributionController
{
    public function getViewModel(Contribution $contribution): ViewModel
    {
        return parent::getViewModel($contribution);
    }

    /**
     * Creates the view model for the gloss editing view.
     */
    public function getEditViewModel(Contribution $contribution)
    {
        return parent::getEditViewModel($contribution);
    }

    /**
     * HTTP GET. Opens a view for editing a gloss contribution.
     *
     * @return array|\Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function edit(Contribution $contribution, Request $request)
    {
        return parent::edit($contribution, $request);
    }

    /**
     * Shows a form for a new contribution.
     *
     * @return array|\Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function create(Request $request)
    {
        throw new \Exception('Not supported implemented');
    }

    public function validateSubstep(Request $request, int $id = 0, int $substepId = 0): mixed
    {
        return false;
    }

    public function validateBeforeSave(Request $request, int $id = 0): bool
    {
        return false;
    }

    public function populate(Contribution $contribution, Request $request)
    {
        return parent::populate($contribution, $request);
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
        return parent::approve($contribution, $request);
    }

    protected function getGlossesFromPayload(array &$payload)
    {
        // Retrieve glosses, which should either be a an array stored
        // upon the data carrier with the key "_translations" (API v.2) or
        // a string with the key "translation", also on the data carrier
        // (API v.1).
        $apiVersion = isset($payload['_translations'])
            ? 2
            : (isset($payload['translation']) ? 1 : 0);

        switch ($apiVersion) {
            case 3:
                // Previously v2 but was updated since, thus obtaining v3
                // structure.
                $glosses = array_map(function ($data) {
                    return new Gloss($data);
                }, $payload['_glosses']);

                unset($payload['_glosses']);
                break;
            case 2:
                $glosses = array_map(function ($data) {
                    return new Gloss($data);
                }, $payload['_translations']);

                unset($payload['_translations']);
                break;

            case 1:
                $glosses = [
                    new Gloss([
                        'translation' => $payload['translation'],
                    ]),
                ];
                break;
            default:
                abort(400, 'Unrecognised payload.');
        }

        return $glosses;
    }

    /**
     * Gets gloss details from the specified array.
     *
     * @param  $glossData  array payload from persistence layer
     * @return array
     */
    protected function getDetailsFromPayload(array &$payload)
    {
        if (! isset($payload['_details'])) {
            return [];
        }

        $details = array_map(function ($data) {
            if (isset($data['gloss_id'])) {
                $data['lexical_entry_id'] = $data['gloss_id'];
                unset($data['gloss_id']);
            }

            return new LexicalEntryDetail($data);
        }, $payload['_details']);
        unset($payload['_details']);

        return $details;
    }
}
