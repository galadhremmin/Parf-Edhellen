<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

/**
 * Turns away crawlers and other self-declared automation by user agent. This keeps noise and load
 * off routes crawlers have no business with; it is not an access control, since any client can
 * send a browser's user agent.
 */
class RejectCrawlers
{
    /**
     * No browser sends a user agent this long, and matching one costs milliseconds.
     */
    const MAX_USER_AGENT_LENGTH = 1024;

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $userAgent = $request->userAgent() ?? '';

        if ($userAgent === '' ||
            strlen($userAgent) > self::MAX_USER_AGENT_LENGTH ||
            (new CrawlerDetect)->isCrawler($userAgent)) {
            abort(403, 'Crawlers and other automated clients are not permitted here.');
        }

        return $next($request);
    }
}
