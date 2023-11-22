@extends('_layouts.default')

@section('title', $author ? $author->nickname : 'Missing account')

@section('body')
  @if ($author === null)
    This is not the droid you are looking for.
  @else
  <div class="view-profile"
        data-inject-module="dashboard-profile"
        data-inject-prop-container="Profile"
        data-inject-prop-account="@json($author)"
        data-inject-prop-statistics="@json($stats)"
        data-inject-prop-view-jumbotron="true"></div>
  @endif
  <hr>
  @include('discuss._standalone', [
    'entity_id'   => $author->id,
    'entity_type' => 'account'
  ])
@endsection
