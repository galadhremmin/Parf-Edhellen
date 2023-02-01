@extends('_layouts.default')

@section('title', 'Found in an external source')

@section('body')

<h2>Found in an external source</h2>

<p>
  What you're looking for doesn't exist in our dictionary but it can be found by visiting {{ $gloss_group->name }}.
  <strong>By clicking this link, you are leaving this website and we will not be in control of what you will see.</strong>
</p>
<p>
    <a class="btn btn-secondary" href="{{ $referer }}" target="_blank">Take me back</a>
    <a class="btn btn-primary" href="{{ $external_url }}" target="_blank">Visit {{ $gloss_group->name }}</a>
</p>

@endsection
