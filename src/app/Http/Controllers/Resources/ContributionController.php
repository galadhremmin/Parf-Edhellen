<?php

namespace App\Http\Controllers\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Jsonable;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\Initialization\Morphs;
use App\Events\{
    ContributionApproved,
    ContributionDestroyed,
    ContributionRejected
};
use App\Models\{
    Contribution,
    Sentence,
    Gloss
};
use App\Http\Controllers\Contributions\ContributionControllerFactory;

class ContributionController extends Controller
{
    /**
     * HTTP GET. Landing page for the current user.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $pendingReviews  = Contribution::whereAccount($user->id)->whereNull('is_approved')
            ->orderBy('id', 'asc')->simplePaginate(10, ['*'], 'pending');
        $rejectedReviews = Contribution::whereAccount($user->id)->where('is_approved', 0)
            ->orderBy('id', 'asc')->simplePaginate(10, ['*'], 'rejected');
        $approvedReviews = Contribution::whereAccount($user->id)->where('is_approved', 1)
            ->orderBy('id', 'asc')->simplePaginate(10, ['*'], 'approved');

        return view('contribution.index', [
            'approvedReviews' => $approvedReviews,
            'rejectedReviews' => $rejectedReviews,
            'pendingReviews'  => $pendingReviews
        ]);
    }
    
    /**
     * HTTP GET. Page for administrators where all reviews are listed.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function list(Request $request)
    {
        $pendingReviews  = Contribution::whereNull('is_approved')
            ->orderBy('id', 'asc')->simplePaginate(10, ['*'], 'pending');
        $rejectedReviews = Contribution::where('is_approved', 0)
            ->orderBy('updated_at', 'desc')->simplePaginate(10, ['*'], 'rejected');
        $approvedReviews = Contribution::where('is_approved', 1)
            ->orderBy('updated_at', 'desc')->simplePaginate(10, ['*'], 'approved');

        return view('contribution.list', [
            'approvedReviews' => $approvedReviews,
            'rejectedReviews' => $rejectedReviews,
            'pendingReviews'  => $pendingReviews
        ]);
    }

    /**
     * HTTP GET. Presents a the specified contribution.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Request $request, int $id) 
    {
        $admin = $request->has('admin')
            ? boolval($request->input('admin'))
            : false;

        $contribution = Contribution::findOrFail($id);
        $this->requestPermission($request, $contribution);

        $model = ContributionControllerFactory::createController($contribution->type)->getViewModel($contribution);
        return view('contribution.show', $model->toModelArray() + [
            'returnToAdminView' => $admin,
            'isAdmin' => $request->user()->isAdministrator()
        ]);
    }

    /**
     * HTTP GET. Presents a form for creating a contribution.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function create(Request $request, string $morph = null)
    {
        $id = $request->has('entity_id')
            ? intval($request->input('entity_id'))
            : 0;

        return ContributionControllerFactory::createController($morph)->create($request, $id);
    }

    /**
     * HTTP GET. Presents a form for re-submitting a rejected contribution, or editing one pending review.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Request $request, int $id) 
    {
        // retrieve the review
        $contribution = Contribution::findOrFail($id);
        $this->requestPermission($request, $contribution);

        if ($contribution->is_approved) {
            $this->contributionAlreadyApproved();
        }

        return ContributionControllerFactory::createController($contribution->type)->edit($contribution, $request);
    }

    /**
     * HTTP GET. Confirm dialogue for deleting a specified submission. 
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function confirmDestroy(Request $request, int $id)
    {
        $contribution = Contribution::findOrFail($id);
        $this->requestPermission($request, $contribution);

        return view('contribution.confirm-destroy', [
            'review' => $contribution
        ]);
    }

    /**
     * HTTP GET. Confirm dialogue for rejecting a specified submission.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function confirmReject(Request $request, int $id)
    {
        $contribution = Contribution::findOrFail($id);
        $this->requestPermission($request, $contribution);

        return view('contribution.confirm-reject', [
            'review' => $contribution
        ]);
    }

    /**
     * HTTP POST. Creates a contribution.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response 
     */
    public function store(Request $request)
    {
        $this->validateAll($request);

        $contribution = new Contribution;
        $contribution->account_id = $request->user()->id;
        $contribution->is_approved = null;
        
        if ($request->has('dependent_on_contribution_id')) {
            $contribution->dependent_on_contribution_id = intval($request->input('dependent_on_contribution_id'));
        }

        $this->saveContribution($contribution, $request);

        return response([
            'id'  => $contribution->id,
            'url' => route('contribution.show', ['contribution' => $contribution->id])
        ], 201);
    }

    /**
     * HTTP PUT. Updates a specified contribution, or creates a new review in the event that
     * the specified review was previously rejected.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response 
     */
    public function update(Request $request, int $id)
    {
        $this->validateAll($request);

        $contribution = Contribution::findOrFail($id);
        $this->requestPermission($request, $contribution);
        if ($contribution->is_approved) {
            $this->contributionAlreadyApproved();
        }

        if ($contribution->is_approved === 0) {
            $contribution = $contribution->replicate();
            $contribution->is_approved = null;
            $contribution->date_reviewed = null;
            $contribution->reviewed_by_account_id = null;
            $contribution->justification = null;
        }

        $this->saveContribution($contribution, $request);

        return response([
            'id'  => $contribution->id,
            'url' => route('contribution.show', ['contribution' => $contribution->id])
        ], 200);
    } 

    /**
     * HTTP PUT. Rejects a specified contribution.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response 
     */
    public function updateReject(Request $request, int $id)
    {
        $contribution = Contribution::findOrFail($id);
        $this->requestPermission($request, $contribution);
        if ($contribution->is_approved) {
            $this->contributionAlreadyApproved();
        }

        $contribution->is_approved = 0;
        $contribution->date_reviewed = Carbon::now();
        $contribution->reviewed_by_account_id = $request->user()->id;
        $contribution->justification = $request->has('justification')
            ? $request->input('justification')
            : null;
        $contribution->save();

        event(new ContributionRejected($contribution));

        return redirect()->route('contribution.show', ['contribution' => $contribution->id]);
    } 

    /**
     * HTTP PUT. Approves a specified contribution.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response 
     */
    public function updateApprove(Request $request, int $id)
    {
        $contribution = Contribution::findOrFail($id);
        $this->requestPermission($request, $contribution);
        if ($contribution->is_approved) {
            $this->contributionAlreadyApproved();
        }

        if ($contribution->dependent_on !== null && ! $contribution->dependent_on->is_approved) {
            abort(400, 'Contribution\'s dependencies are not approved.');
        }

        $id = ContributionControllerFactory::createController($contribution->type)->approve($contribution, $request);

        $contribution->is_approved = 1;
        $contribution->date_reviewed = Carbon::now();
        $contribution->reviewed_by_account_id = $request->user()->id;
        $contribution->approved_as_entity_id = $id;
        $contribution->save();

        event(new ContributionApproved($contribution));

        return redirect()->route('contribution.show', ['contribution' => $contribution->id]);
    }

    /**
     * HTTP DELETE. Deletes a specified contribution.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response 
     */
    public function destroy(Request $request, int $id)
    {
        $contribution = Contribution::findOrFail($id);
        $this->requestPermission($request, $contribution);

        if ($contribution->is_approved) {
            abort(400, 'You cannot delete an approved contribution.');
        }

        $contribution->dependencies()->delete();
        $contribution->delete();

        event(new ContributionDestroyed($contribution));
        
        return $request->user()->isAdministrator()
            ? redirect()->route('contribution.list')
            : redirect()->route('contribution.index');
    }

    /**
     * HTTP POST. Validate a sub-step in the contribution process.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response 
     */
    public function validateSubstep(Request $request)
    {
        $this->validateContributionRequest($request);

        $substepId = $request->has('substep_id')
            ? intval($request->input('substep_id'))
            : 0;
        
        $id = $this->getEntityId($request);
        
        $result = ContributionControllerFactory::createController($request)->validateSubstep($request, $id, $substepId);
        if (is_bool($result)) {
            return response(null, $result ? 204 : 400);
        }

        return $result;
    }

    /**
     * Validates the specified request according to the morph identified
     * by the request input parameters. The request parameter ("morph") is
     * required.
     *
     * @param Request $request
     * @param int $id
     * @return void
     */
    protected function validateAll(Request $request)
    {
        $this->validateContributionRequest($request);

        $id = $this->getEntityId($request);

        ContributionControllerFactory::createController($request)->validateBeforeSave($request, $id);
    }

    protected function getEntityId(Request $request)
    {
        if (! $request->has('id')) {
            return 0;
        }

        $entityId = intval($request->input('id'));
        return $entityId;
    }

    /**
     * Validates the request to ensure that it is compatible with the requirements of a contribution
     * request.
     *
     * @param Request $request
     * @return void
     */
    protected function validateContributionRequest(Request $request)
    {
        $this->validate($request, [
            'morph'                        => 'required|string',
            'contribution_id'              => 'sometimes|numeric|exists:contributions,id',
            'dependent_on_contribution_id' => 'sometimes|nullable|numeric|exists:contributions,id'
        ]);
    }

    /**
     * Requests permission for the specified contribution based on the information associated with
     * the specified request.
     *
     * @param Request $request
     * @param Contribution $model
     * @return void
     */
    protected function requestPermission(Request $request, Contribution $model)
    {
        $user = $request->user();

        if ($user->isAdministrator()) {
            return;
        }

        if ($user->id === $model->account_id) {
            return;
        }

        abort(403);
    }

    /**
     * Updates the specified contribution based on the infromation provided by the request.
     *
     * @param Contribution $contribution
     * @param Request $request
     * @return void
     */
    protected function saveContribution(Contribution $contribution, Request $request)
    {
        $controller = ContributionControllerFactory::createController($request);
        $entity = $controller->populate($contribution, $request);

        $type = Morphs::getAlias($entity);
        if ($type === null) {
            abort(400, 'Unrecognised type of contribution.');
        }

        $contribution->type  = $type;
        $contribution->notes = $request->has('notes') 
            ? $request->input('notes') 
            : null;

        // payloads might already be configured at this point, either by the save methods
        // or earlier in the call stack.
        if (! $controller->disableChangeDetection() && ! $contribution->isDirty('payload')) {
            $contribution->payload = $entity instanceof Jsonable
                ? $entity->toJson()
                : json_encode($entity);
        }

        $contribution->save();
    }

    protected function contributionAlreadyApproved() 
    {
        abort(400, 'Contribution is already approved.');
    }
}
