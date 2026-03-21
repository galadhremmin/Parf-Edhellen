<?php

namespace App\Http\Controllers\Resources;

use App\Adapters\AuditTrailAdapter;
use App\Adapters\QueueJobStatisticAdapter;
use App\Http\Controllers\Abstracts\Controller;
use App\Models\FailedJob;
use App\Models\SystemError;
use App\Repositories\AuditTrailRepository;
use App\Repositories\QueueJobStatisticRepository;
use App\Repositories\TrendingRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemErrorController extends Controller
{
    const MaxAuditTrailEntriesPerPage = 30;

    private AuditTrailRepository $_auditTrailRepository;

    private AuditTrailAdapter $_auditTrailAdapter;

    private QueueJobStatisticAdapter $_queueJobStatisticAdapter;

    private QueueJobStatisticRepository $_queueJobStatisticRepository;

    private TrendingRepository $_trendingRepository;

    public function __construct(AuditTrailRepository $auditTrailRepository, AuditTrailAdapter $auditTrailAdapter, QueueJobStatisticAdapter $queueJobStatisticAdapter, QueueJobStatisticRepository $queueJobStatisticRepository, TrendingRepository $trendingRepository)
    {
        $this->_auditTrailRepository = $auditTrailRepository;
        $this->_auditTrailAdapter = $auditTrailAdapter;
        $this->_queueJobStatisticAdapter = $queueJobStatisticAdapter;
        $this->_queueJobStatisticRepository = $queueJobStatisticRepository;
        $this->_trendingRepository = $trendingRepository;
    }

    public function index(Request $request)
    {
        $errorsByWeek = $this->getRowCountPerWeek(SystemError::class, 'category');

        $auditTrailPage = $request->query('audit_trail_page', 0);
        $auditTrailEntries = $this->_auditTrailAdapter->adapt(
            $this->_auditTrailRepository->get(self::MaxAuditTrailEntriesPerPage, $auditTrailPage * self::MaxAuditTrailEntriesPerPage)
        );

        $jobsByQueue = DB::table('jobs')
            ->select('queue', DB::raw('COUNT(*) as count'))
            ->groupBy('queue')
            ->pluck('count', 'queue');

        $jobStatsByQueue = $this->_queueJobStatisticAdapter->adapt(
            $this->_queueJobStatisticRepository->getStatisticsByQueue(
                Carbon::now()->subDays(30),
                Carbon::now(),
            )
        );

        $viewsPerDay = $this->_trendingRepository->getViewsPerHour(Carbon::now()->subDays(30), Carbon::now());

        return view('admin.dashboard.index', [
            'auditTrailEntries' => $auditTrailEntries,
            'auditTrailPage' => $auditTrailPage,
            'errorsByWeek' => $errorsByWeek['count_by_week'],
            'errorCategories' => $errorsByWeek['categories'],
            'hasFailedJobs' => FailedJob::exists(),
            'jobsByQueue' => $jobsByQueue,
            'jobStatsByQueue' => $jobStatsByQueue,
            'viewsPerDay' => $viewsPerDay,
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
            DB::raw($category.' AS category'),
        ];

        $groupByFields = [
            DB::raw('year'),
            DB::raw('week'),
            DB::raw('year_week'),
            DB::raw('category'),
        ];

        $model = new $modelName;
        $countByWeek = $model::select($selectFields)
            ->whereNotIn($category, ['http-401', 'http-404'])
            ->groupBy($groupByFields)
            ->get();

        $categories = $countByWeek->pluck('category')->unique()->values();
        $countByWeek = $countByWeek->reduce(function ($carry, $item) {
            $key = $item->year_week;
            $value = $item->number_of_errors;
            $category = $item->category;

            if (isset($carry[$key])) {
                $carry[$key][$category] = $value;
            } else {
                $carry[$key] = [
                    'year_week' => $item->year_week,
                    'week' => $item->week,
                    'year' => $item->year,
                    $category => $value,
                ];
            }

            return $carry;
        }, []);

        return [
            'count_by_week' => collect(array_values($countByWeek)),
            'categories' => $categories,
        ];
    }
}
