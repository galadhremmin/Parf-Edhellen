<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\{
    Account, 
    FlashcardResult, 
    Contribution,
    SystemError
};

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

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
        $user->setIncognito( boolval($request->input('incognito')) );

        return redirect()->route('dashboard');
    }
}
