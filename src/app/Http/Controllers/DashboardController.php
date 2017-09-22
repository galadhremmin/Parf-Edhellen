<?php

namespace App\Http\Controllers;

use App\Models\{Account, FlashcardResult, Contribution};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $noOfFlashcards = FlashcardResult::forAccount($user->id)->count();
        $noOfContributions = Contribution::forAccount($user->id)->count();
        $noOfPendingContributions = $user->isAdministrator()
            ? Contribution::whereNull('is_approved')->count()
            : 0;
        $incognito = $user->isIncognito();

        return view('dashboard.index', [
            'user' => $request->user(),
            'noOfFlashcards' => $noOfFlashcards,
            'noOfContributions' => $noOfContributions,
            'noOfPendingContributions' => $noOfPendingContributions,
            'incognito' => $incognito
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
