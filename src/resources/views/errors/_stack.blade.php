@if (Auth::check() && Auth::user()->isAdministrator() && isset($exception))
<hr>
<p>{{ $exception->getMessage() }}</p>
<pre>{{ $exception->getTraceAsString() }}</pre>
@endif
