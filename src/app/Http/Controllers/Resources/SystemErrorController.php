<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

use App\Models\{
    SystemError,
    FailedJob
};
use App\Http\Controllers\Abstracts\Controller;

class SystemErrorController extends Controller
{
    public function index(Request $request)
    {
        $errorsByWeek = $this->getRowCountPerWeek(SystemError::class, 'category');
        $failedJobsByWeek = $this->getRowCountPerWeek(FailedJob::class, 'queue', 'failed_at');

        return view('admin.system-error.index', [
            'errorsByWeek'         => $errorsByWeek['count_by_week'],
            'errorCategories'      => $errorsByWeek['categories'],
            'failedJobsByWeek'     => $failedJobsByWeek['count_by_week'],
            'failedJobsCategories' => $failedJobsByWeek['categories']
        ]);
    }

    public function testConnectivity(Request $request, string $component)
    {
        $className = 'App\\Monitors\\'.$component;
        if (! class_exists($className)) {
            abort(400, sprintf('Unrecognised monitor %s.', $className));
        }

        try {
            $entity = resolve($className);

            if (! method_exists($entity, 'testOnce')) {
                abort(400, sprintf('Monitor %s does not support self-checks.', $className));
            }

            $entity->testOnce();
        } catch (\Exception $ex) {
            // Failed
            return $ex;
        }

        return 'OK';
    }

    private function getRowCountPerWeek(string $modelName, string $category, string $dateColumn = 'created_at')
    {
        $selectFields = [
            DB::raw('YEAR('.$dateColumn.') AS year'), 
            DB::raw('WEEK('.$dateColumn.') AS week'),
            DB::raw('CONCAT(YEAR('.$dateColumn.'), "/",  WEEK('.$dateColumn.')) AS year_week'),
            DB::raw('COUNT(*) AS number_of_errors'),
            DB::raw($category.' AS category')
        ];

        $groupByFields = [
            DB::raw('year'),
            DB::raw('week'),
            DB::raw('year_week'),
            DB::raw('category')
        ];

        $model = new $modelName;
        $countByWeek = $model::select($selectFields)
            ->groupBy($groupByFields)
            ->get();

        $categories = $countByWeek->pluck('category')->unique()->values();
        $countByWeek = $countByWeek->reduce(function ($carry, $item) {
                $key      = $item->year_week;
                $value    = $item->number_of_errors;
                $category = $item->category;
    
                if (isset($carry[$key])) {
                    $carry[$key][$category] = $value;
                } else {
                    $carry[$key] = [
                        'year_week' => $item->year_week,
                        'week'      => $item->week,
                        'year'      => $item->year,
                        $category   => $value
                    ];
                }
    
                return $carry;
            }, []);
        
        return [
            'count_by_week' => collect(array_values($countByWeek)),
            'categories'    => $categories
        ];
    }
}
