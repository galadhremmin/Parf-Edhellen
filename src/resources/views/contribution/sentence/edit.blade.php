@extends('_layouts.default')

@section('title', 'Change contribution')
@section('body')
  <h1>Change contribution #{{ $review->id }}</h1>
  
  {!! Breadcrumbs::render('contribution.edit', $review->id) !!}

  <div id="ed-sentence-form"
    data-inject-module="form-sentence"
    data-inject-prop-sentence="@json($sentence)"
    data-inject-prop-sentence-fragments="@json($fragmentData)"
    data-inject-prop-sentence-transformations="@json($transformations)"
    data-inject-prop-sentence-translations="@json($translations)"
    data-inject-prop-confirm-button="{{ $review->is_approved === null ? 'Save changes' : 'Resubmit for review' }}"
  ></div>

@endsection
