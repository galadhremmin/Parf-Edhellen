@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Contribute')
@section('body')
  <h1>Contribution #{{ $review->id }}</h1>
  
  {!! Breadcrumbs::render('contribution.show', $review->id) !!}

  @include('contribution._status-alert', $review)

  <div class="well">
    @foreach ($sections as $section)
      @foreach ($section['glosses'] as $gloss)
        @include('book._gloss', [ 
          'gloss' => $gloss, 
          'language' => $section['language'],
          'disable_tools' => true
        ])
      @endforeach
    @endforeach

    @foreach ($keywords as $keyword) 
      <span class="label label-default">{{ $keyword }}</span>
    @endforeach
  </div>

  @if (! empty($review->notes))
  <div class="well">
    <strong>Author's notes</strong>
    <p>{{ $review->notes }}</p>
  </div>
  @endif

  @if (! $review->is_approved)
    @include('contribution._pending-info', $review)
  @else
  <hr>
  You can <a href="{{ $link->translation($review->translation_id) }}">visit the gloss in the dictionary</a>.
  @endif
  <hr>
  @include('_shared._comments', [
    'entity_id' => $review->id,
    'context'   => 'contribution',
    'enabled'   => true
  ])

@endsection

@section('scripts')
  <script type="text/javascript" src="/js/comment.js" async></script>
@endsection
