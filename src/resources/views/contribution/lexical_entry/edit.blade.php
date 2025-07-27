@extends('_layouts.default')

@section('title', 'Change contribution')
@section('body')
  <h1>Change contribution #{{ $review->id }}</h1>
  
  {!! Breadcrumbs::render('contribution.edit', $review->id) !!}

  <div id="ed-gloss-form"
    data-inject-module="form-gloss"
    data-inject-prop-lexical-entry="@json($payload)"
    data-inject-prop-inflections="@json($inflections)"
    @if (isset($form_restrictions)) data-inject-prop-form-sections="@json($form_restrictions)" @endif
    data-inject-prop-confirm-button="{{ $review->is_approved === null ? 'Save changes' : 'Resubmit for review' }}"
  ></div>

@endsection
