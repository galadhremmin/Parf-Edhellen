<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Http\Request;
use Auth;
use DB;

use App\Models\SystemError;
use App\Http\Controllers\Controller;

class SystemErrorController extends Controller
{
    public function index(Request $request)
    {
        $errorsByWeek = SystemError::select( 
            DB::raw('year(created_at) as year'), 
            DB::raw('week(created_at) as week'),
            DB::raw('count(*) as number_of_errors'),
            'category')
            ->groupBy(DB::raw('year(created_at)'), DB::raw('week(created_at)'), 'category')
            ->get();

        $errors = SystemError::orderBy('id', 'desc')
            ->simplePaginate(30);
        
        $model = [
            'errors' => $errors,
            'errorsByWeek' => $errorsByWeek
        ];

        return $request->ajax() ? $model : view('system-error.index', [ 'model' => $model]);
    }
}
