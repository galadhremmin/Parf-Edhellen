<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\Speech;
use Illuminate\Http\Request;

class SpeechApiController extends Controller
{
    public function index(Request $request, int $id = 0)
    {
        if ($id !== 0) {
            $speech = Speech::find($id);
            if ($speech === null) {
                return response(null, 404);
            }

            return $speech;
        }

        return Speech::orderBy('name')->get();
    }
}
