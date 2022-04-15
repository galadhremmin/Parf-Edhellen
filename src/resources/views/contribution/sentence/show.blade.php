@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Contribute')
@section('body')
  <h1>Contribution #{{ $review->id }}</h1>
  
  {!! Breadcrumbs::render('contribution.show', $review->id, $admin) !!}

  @include('contribution._status-alert', $review)

  @if (isset($originalSentence))
  <p>
    <span class="TextIcon TextIcon--info-sign"></span>
    This is a proposed modification of the phrase  
    <a href="{{ $link->sentence($originalSentence->language_id, $originalSentence->language->name, $originalSentence->id, $originalSentence->name) }}">
      {{ $originalSentence->name }}
    </a>.
  </p>
  @endif

  <div class="card">
    <div class="card-body">
      <h2>{{ $sentence->name }}</h2>

      @if (!empty($sentence->description))
      @markdown($sentence->description)
      @endif

      @markdown($sentence->long_description)

      <div id="ed-fragment-navigator" data-inject-module="sentence-inspector" data-inject-prop-sentence="@json($fragmentData)"></div>

      <p>
        <span class="badge bg-secondary">{{ $sentence->language->name }}</span>
        @if ($sentence->is_neologism)
        <span class="badge bg-secondary">Neologism</span>
        @endif
      </p>
    </div>
  </div>

  @include('contribution._notes', $review)
  @include('contribution._pending-info', $review)

  <hr>  
  @include('discuss._standalone', [
    'entity_id'   => $review->id,
    'entity_type' => 'contribution'
  ])

@endsection
