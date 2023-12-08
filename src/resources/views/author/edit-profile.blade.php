@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', $author ? 'Change profile ' . $author->nickname : 'Missing account')

@section('body')
  @if ($author === null)
    This is not the droid you are looking for.
  @else
  
  <section class="edit-profile"
          data-inject-module="dashboard-profile"
          data-inject-prop-container="ProfileForm"
          data-inject-prop-account="@json($author)"></section>

  @endif
@endsection
