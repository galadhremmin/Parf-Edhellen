<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Repositories\SystemErrorRepository;
use Symfony\Component\HttpFoundation\Response;

class LogExpensiveRequests
{
    /**
     * The maximum request duration in milliseconds before logging as expensive.
     *
     * @var int
     */
    protected $threshold;

    private SystemErrorRepository $_systemErrorRepository;

    /**
     * Create a new middleware instance.
     */
    public function __construct(SystemErrorRepository $systemErrorRepository)
    {
        $this->threshold = config('ed.expensive_request_threshold', 2000); // Default 2 seconds
        $this->_systemErrorRepository = $systemErrorRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int|null  $threshold  Optional threshold override in milliseconds
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?int $threshold = null): Response
    {
        $startTime = microtime(true);
        
        // Use provided threshold or default
        $requestThreshold = $threshold ?? $this->threshold;
        
        // Process the request
        $response = $next($request);
        
        // Calculate duration
        $duration = round((microtime(true) - $startTime) * 1000, 2); // Convert to milliseconds
        
        // Log if request exceeds threshold
        if ($duration > $requestThreshold) {
            $this->logExpensiveRequest($request, $response);
        }
        
        return $response;
    }

    /**
     * Log the expensive request with detailed information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @param  float  $duration
     * @param  int  $threshold
     * @return void
     */
    protected function logExpensiveRequest(Request $request, Response $response): void
    {
        $this->_systemErrorRepository->saveExpensiveRequest($request, $response);
    }
}
