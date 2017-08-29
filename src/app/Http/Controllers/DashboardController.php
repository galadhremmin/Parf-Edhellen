<?php

namespace App\Http\Controllers;

use App\Models\{FlashcardResult, TranslationReview};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $noOfFlashcards = FlashcardResult::forAccount($user->id)->count();
        $noOfContributions = TranslationReview::forAccount($user->id)->count();

        return view('dashboard.index', [
            'user' => $request->user(),
            'noOfFlashcards' => $noOfFlashcards,
            'noOfContributions' => $noOfContributions
        ]);
    }
}
