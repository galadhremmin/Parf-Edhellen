@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Contribute')
@section('body')
  <h1>Contribution #{{ $review->id }}</h1>
  
  {!! Breadcrumbs::render('contribution.show', $review->id) !!}

  @include('contribution._status-alert', $review)

  @if (isset($originalSentence))
  <p>
    <span class="glyphicon glyphicon-info-sign"></span>
    This is a proposed modification of the phrase  
    <a href="{{ $link->sentence($originalSentence->language_id, $originalSentence->language->name, $originalSentence->id, $originalSentence->name) }}">
      {{ $originalSentence->name }}
    </a>.
  </p>
  @endif

  <h2>{{ $sentence->name }}</h2>

  @if (!empty($sentence->description))
  @markdown($sentence->description)
  @endif

  <div id="ed-fragment-navigator"></div>
  <script type="application/json" id="ed-preload-sentence-data">{!! $fragmentData !!}</script>

  @markdown($sentence->long_description)

  <p>
    <span class="label label-default">{{ $sentence->language->name }}</span>
    @if ($sentence->is_neologism)
    <span class="label label-default">Neologism</span>
    @endif
  </p>

  @include('contribution._notes', $review)
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
