@extends('_layouts.default')

@section('title', $author ? $author->nickname : 'Missing account')

@section('body')
  @if ($author === null)
    This is not the droid you are looking for.
  @else
  @ssr('dashboard-profile', [
    'container'     => 'Profile',
    'account'       => $author,
    'statistics'    => $stats,
    'showJumbotron' => 'true',
    'showDiscuss'   => 'true',
    'showProfile'   => 'true'
  ])
  @endif
@endsection
