<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Models\Inflection;
use App\Http\Controllers\Controller;

class InflectionApiController extends Controller 
{
    public function index(Request $request, int $id = 0)
    {
        if ($id !== 0) {
            $inflection = Inflection::find($id);
            if ($inflection === null) {
                return response(null, 404);
            }

            return $inflection;
        }

        return Inflection::orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->groupBy('group_name');
    }
}