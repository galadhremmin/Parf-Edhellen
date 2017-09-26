<?php

namespace App\Http\Controllers\Contributions;

use Illuminate\Http\Request;
use App\Models\Contribution;

interface IContributionController
{
    /**
     * Shows the specified contribution
     *
     * @param Contribution $contribution
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    function show(Contribution $contribution);

    /**
     * Shows an editing form for the specified contribution
     *
     * @param Contribution $contribution
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    function edit(Contribution $contribution, Request $request);

    /**
     * Performs partial validation of the specified request.
     *
     * @param Request $request
     * @param int $id
     * @param int $substepId
     * @return void
     */
    function validateSubstep(Request $request, int $id = 0, int $substepId = 0);

    /**
     * Performs complete validation of the specified request.
     *
     * @param Request $request
     * @param int $id
     * @return void
     */
    function validateBeforeSave(Request $request, int $id = 0);

    /**
     * Populates the specified contribution payload with the information passed through
     * the request's input parameters. Returns the resulting entity.
     *
     * @param Contribution $contribution
     * @param Request $request
     * @return App\Models\ModelBase
     */
    function populate(Contribution $contribution, Request $request);

    /**
     * Approves the specified contribution by transforming it into a respective entity.
     * Populates the contribution with meta-data.
     *
     * @param Contribution $contribution
     * @param Request $request
     * @return void
     */
    function approve(Contribution $contribution, Request $request);
}
