<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Abstracts\Controller;
use App\Helpers\StorageHelper;
use App\Models\{
    FlashcardResult, 
    Contribution,
    SystemError
};

class DashboardController extends Controller
{
    protected $_storageHelper;

    public function __construct(StorageHelper $storageHelper)
    {
        $this->_storageHelper = $storageHelper;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->has_avatar) {
            $user->avatar_path = $this->_storageHelper->accountAvatar($user, false /* = _null_ if none exists */);
        }

        $noOfFlashcards = FlashcardResult::forAccount($user->id)->count();
        $noOfContributions = Contribution::forAccount($user->id)->count();

        $isAdmin = $user->isAdministrator();
        $isIncognito = $user->isIncognito();
        $noOfPendingContributions = $isAdmin
            ? Contribution::whereNull('is_approved')->count()
            : 0;
        $numberOfErrors = $isAdmin
            ? SystemError::count()
            : 0;

        return view('dashboard.index', [
            'user'              => $request->user(),
            'noOfFlashcards'    => $noOfFlashcards,
            'noOfContributions' => $noOfContributions,
            'noOfPendingContributions' => $noOfPendingContributions,
            'incognito'         => $isIncognito,
            'numberOfErrors'    => $numberOfErrors
        ]);
    }

    public function setIncognito(Request $request)
    {
        $this->validate($request, [
            'incognito' => 'required|boolean'
        ]);

        $user = $request->user();
        if ($user->isAdministrator()) {
            $user->setIncognito( boolval($request->input('incognito')) );
        }

        return redirect()->route('dashboard');
    }
}
