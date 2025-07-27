<?php

namespace App\Http\Controllers\Contributions;

use App\Adapters\BookAdapter;
use App\Http\Controllers\Abstracts\Controller;
use App\Http\Controllers\Traits\CanMapGloss;
use App\Http\Controllers\Traits\CanValidateGloss;
use App\Models\Account;
use App\Models\Contribution;
use App\Models\LexicalEntry;
use App\Models\LexicalEntryDetail;
use App\Models\Sense;
use App\Models\Gloss;
use App\Models\Word;
use App\Repositories\LexicalEntryInflectionRepository;
use App\Repositories\LexicalEntryRepository;
use Illuminate\Database\Eloquent\Collection;
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
}
