@extends('_layouts.default')

@section('title', 'Discussion')
@section('body')
  
  {!! Breadcrumbs::render('discuss.group', $group) !!}

  <h1>Discussion about {{ $group->name }}</h1>
  
  <div class="discuss-thread-tools" data-inject-module="discuss-threads-tools" data-inject-prop-group-id="{{ $group->id }}" data-inject-prop-group-name="{{ $group->name }}"></div>

  <hr>

  <div class="discuss-table">
  @if (count($threads) < 1)
  <p>
    <span class="glyphicon glyphicon-info-sign"></span>
    There are currently no threads associated with this subject.
  </p>
  @else

  @include('discuss._threads', [
    'threads' => $threads->where('is_sticky', 1),
    'name'    => 'Pinned threads'
  ])
  @include('discuss._threads', [
    'threads' => $threads->where('is_sticky', 0),
    'name'    => 'All threads'
  ])
  @endif
  </div>

  <div data-inject-module="discuss-feed" data-inject-prop-group-id="{{ $group->id }}"></div>

  @include('discuss._pagination', [
    'pages' => $pages,
    'current_page' => $current_page,
    'no_of_pages' => $no_of_pages
  ])

@endsection
