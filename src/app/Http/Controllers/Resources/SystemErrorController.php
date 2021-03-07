<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

use App\Models\SystemError;
use App\Http\Controllers\Abstracts\Controller;

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
            ->where('created_at', '>=', Carbon::today()->addMonth(-18))
            ->get()
            ->groupBy(function ($item) {
                return sprintf('%d/%d', $item['year'], $item['week']);
            });
        
        $errorsByWeek = $errorsByWeek->keys()
            ->map(function ($week) use ($errorsByWeek) {
                return $errorsByWeek[$week]->reduce(function ($carry, $item) use ($week) {
                    $carry['week'] = $week;
                    $carry[$item['category']] = $item['number_of_errors'];
                    return $carry;
                }, []);
            });

        return view('system-error.index', [ 'errorsByWeek' => $errorsByWeek]);
    }
}
