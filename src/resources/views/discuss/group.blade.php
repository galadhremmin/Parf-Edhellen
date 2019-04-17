@inject('linker', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Discussion')
@section('body')
  
  {!! Breadcrumbs::render('discuss.group', $group) !!}

  <h1>Discussion about {{ $group->name }}</h1>
  
  @if (Auth::check())
  <div class="text-right">
    <a href="{{ route('discuss.create') }}" class="btn btn-primary">
      <span class="glyphicon glyphicon-pencil"></span>
      New thread
    </a>
  </div>
  @endif

  <hr>

  <div class="discuss-table">
  @if (count($threads) < 1)
  <p>
    <span class="glyphicon glyphicon-info-sign"></span>
    There are currently no threads associated with this subject.
  </p>
  @else
  @foreach ($threads as $thread)
    <div class="r {{ $thread->is_sticky ? 'sticky' : '' }}">
      <div class="c">
        @include('discuss._avatar', ['account' => $thread->account])
      </div>
      <div class="c p2">
        <a href="{{ $linker->forumThread($group->id, $group->name, $thread->id, $thread->normalized_subject) }}">
          @if ($thread->is_sticky)
          <span class="glyphicon glyphicon-pushpin" title="This post has been pinned to the top by an administrator."></span>
          @endif
          {{ $thread->subject }}
        </a>
        <div class="pi">
          {{ $thread->account ? $thread->account->nickname : 'nobody' }}
          <span class="date">{{ $thread->updated_at ?: $thread->created_at }}</span>
        </div>
      </div>
      <div class="c text-right">
        {{ $thread->number_of_posts }} <span class="glyphicon glyphicon-comment"></span>
        {{ $thread->number_of_likes }} <span class="glyphicon glyphicon-thumbs-up"></span>
      </div>
    </div>
  @endforeach
  @endif
  </div>

  <div data-inject-module="discuss-feed" data-inject-prop-group-id="{{ $group->id }}"></div>

  @include('discuss._pagination', [
    'pages' => $pages,
    'current_page' => $current_page,
    'no_of_pages' => $no_of_pages
  ])

@endsection
@section('styles')
<link rel="stylesheet" href="@assetpath(style-discuss.css)">
@endsection
@section('scripts')
@endsection
