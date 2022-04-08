@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Comments by '.$author->nickname)

@section('body')
  @if ($noOfPosts > 0)
  <div class="page-header">
    <h1>Comments by {{ $author->nickname }}</h1>
  </div>
  <p>These are the {{ count($posts) }} posts of {{ $noOfPosts }} by <a href="{{ $link->author($author->id, $author->nickname) }}">{{ $author->nickname }}</a>.</p>
  <ul class="timeline">
    @foreach ($posts as $post)
      <li class="{{ $post->inverted ? 'timeline-inverted' : '' }}">
        @if (! $post->i) 
        <div class="timeline-badge info"><i class="glyphicon glyphicon-{{ $post->icon }}"></i></div>
        @endif
        <div class="timeline-panel">
          <div class="timeline-heading">
            <h4 class="timeline-title">{{ $post->subject }}</h4>
            <p>
              <small class="text-muted">
                <i class="glyphicon glyphicon-time"></i> 
                <span class="date">{{ $post->created_at }}</span>
              </small>
            </p>
          </div>
          <div class="timeline-body">
            @markdown($post->content)
            <hr>
            <div class="{{ $post->inverted ? 'text-end' : '' }}">
              <a href="{{ $link->forumThread($post->forum_group_id, 'g', $post->forum_thread_id, $post->subject_path, $post->id) }}" class="btn btn-sm btn-secondary">
                <span class="glyphicon glyphicon-envelope"></span>
                View thread
              </a>
            </div>
          </div>
        </div>
      </li>
    @endforeach
  </ul>
  @if ($noOfPages > 1)
  <hr>
  <div class="text-center">
    <nav aria-label="Page navigation">
      <ul class="pagination">
        <li class="{{ $page < 1 ? 'disabled' : '' }}">
          <a href="{{ route('author.posts', ['id' => $author->id, 'page' => max(0, $page - 1)]) }}" aria-label="Previous">
            <span aria-hidden="true">&larr;</span>
            Previous
          </a>
        </li>
        @for ($i = 0; $i < $noOfPages; $i += 1)
        <li class="{{ $page === $i ? 'active' : '' }}"><a href="{{ route('author.posts', ['id' => $author->id, 'page' => $i]) }}">{{ $i + 1 }}</a></li>
        @endfor
        <li class="{{ $page >= $noOfPages - 1 ? 'disabled' : '' }}">
          <a href="{{ route('author.posts', ['id' => $author->id, 'page' => min($noOfPages - 1, $page + 1)]) }}" aria-label="Next">
            Next
            <span aria-hidden="true">&rarr;</span>
          </a>
        </li>
      </ul>
    </nav>
  </div>
  @endif
  @else
  <em>There are no recorded posts by this account.</em>
  @endif
@endsection
@section('styles')
<link rel="stylesheet" href="@assetpath(style-timeline.css)">
@endsection