@extends('_layouts.default')

@section('title', 'Server error')
@section('body')

<h1>Ai! Latta quinganyava ná racína!</h1>

<p>
    A server error has unfortunately resulted in an unrecoverable error. This means that the content
    you were requesting can't be served at this time. This error has been logged and will be fixed.
</p>
<p>
    You can try to refresh the page, and if that doesn't work, try again later.
</p>
<p>
    Sorry for the inconvenience!
</p>

@if (Auth::check() && Auth::user()->isAdministrator())
<hr>
<p>{{ $exception->getMessage() }}</p>
<pre>{{ $exception->getStack() }}</pre>
@endif

@endsection
