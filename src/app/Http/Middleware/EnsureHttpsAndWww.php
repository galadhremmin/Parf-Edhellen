<?php

namespace App\Http\Middleware;

use \Illuminate\Auth\AuthenticationException;
use \Illuminate\Contracts\Foundation\Application;
use \Illuminate\Http\Request;
use Closure;

class EnsureHttpsAndWww
{
    protected $_appUrl;
    protected $_expects;
    protected $_expectsLength;
    protected $_isSecure;

    public function __construct(Application $app) {
        $appUrl = config('app.url');
        $parts = parse_url($appUrl);

        $urlLength = strlen($appUrl);
        $this->_appUrl        = $urlLength > 0 && $appUrl[$urlLength - 1] === '/' 
            ? substr($appUrl, 0, $urlLength - 1) : $appUrl;
        $this->_expects       = $parts['scheme'] . '://' . $parts['host'];
        $this->_expectsLength = strlen($this->_expects);
        $this->_isSecure      = $parts['scheme'] === 'https';
    } 

    /**
     * Ensures that the request is handled over HTTPS.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // retrieve the first part of the URL, which should match the expected prefix
        $urlPart = substr($request->fullUrl(), 0, $this->_expectsLength);

        if ($urlPart !== $this->_expects) {
            $path = $request->path();
            $correctUrl = $this->_appUrl.(strlen($path) > 0 && $path[0] !== '/' ? '/' : '').$path;

            if (!$this->_isSecure) {
                return redirect()->to($correctUrl);
            }

            // for Proxies
            Request::setTrustedProxies([$request->getClientIp()]);
            return redirect()->secure($correctUrl);
        }

        return $next($request);
    }

}
