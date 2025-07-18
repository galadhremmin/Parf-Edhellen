<?php

namespace App\Http\Controllers\Contributions;

use App\Models\Contribution;
use Illuminate\Http\Request;

interface IContributionController
{
    /**
     * Retrieves a view model with everything needed to render the particular contribution.
     */
    public function getViewModel(Contribution $contribution): ViewModel;

    /**
     * Shows an editing form for the specified contribution
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function edit(Contribution $contribution, Request $request);

    /**
     * Shows a form for a new contribution.
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function create(Request $request);

    /**
     * Performs partial validation of the specified request.
     *
     * @return bool
     */
    public function validateSubstep(Request $request, int $id = 0, int $substepId = 0): bool;

    /**
     * Performs complete validation of the specified request.
     *
     * @return bool
     */
    public function validateBeforeSave(Request $request, int $id = 0): bool;

    /**
     * Populates the specified contribution payload with the information passed through
     * the request's input parameters. Returns the resulting entity.
     *
     * @return App\Models\ModelBase
     */
    public function populate(Contribution $contribution, Request $request);

    /**
     * Returns whether the controller should be in full control of the population of the payload,
     * and all automatic change detection in the parent controller should be ignored.
     */
    public function disableChangeDetection(): bool;

    /**
     * Approves the specified contribution by transforming it into a respective entity.
     * Populates the contribution with meta-data.
     *
     * @return int Entity primary ID
     */
    public function approve(Contribution $contribution, Request $request): int;
}
