@extends('_layouts.default')

@section('title', $author ? $author->nickname : 'Missing account')

@section('body')
  @if ($author === null)
    This is not the droid you are looking for.
  @else
  <div data-inject-module="dashboard-profile"
       data-inject-prop-container="Profile"
       data-inject-prop-account="@json($author)"
       data-inject-prop-statistics="@json($stats)"
       data-inject-prop-show-jumbotron="true"
       data-inject-prop-show-discuss="true"
       data-inject-prop-show-profile="true"></div>
  @endif
@endsection
