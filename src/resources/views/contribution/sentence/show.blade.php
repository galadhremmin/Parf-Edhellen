@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Contribute')
@section('body')
  <h1>Contribution #{{ $review->id }}</h1>
  
  {!! Breadcrumbs::render('contribution.show', $review->id) !!}

  @include('contribution._status-alert', $review)

  <h2>{{ $sentence->name }}</h2>

  @if (!empty($sentence->description))
  <p>
    {{ $sentence->description }}
  </p>
  @endif

  <div id="ed-fragment-navigator"></div>
  <script type="application/json" id="ed-preload-sentence-data">{!! $fragmentData !!}</script>

  {!! $sentence->long_description !!}

  <hr>
  @include('contribution._pending-info', $review)
  <hr>
  @include('_shared._comments', [
    'entity_id' => $review->id,
    'context'   => 'contribution',
    'enabled'   => true
  ])

@endsection

@section('scripts')
  <script type="text/javascript" src="/js/sentence.js" async></script>
  <script type="text/javascript" src="/js/comment.js" async></script>
@endsection
