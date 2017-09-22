@extends('_layouts.default')

@section('title', 'Change contribution')
@section('body')
  <h1>Change contribution #{{ $review->id }}</h1>
  
  {!! Breadcrumbs::render('contribution.edit', $review->id) !!}
  <div id="ed-translation-form" data-admin="false" data-confirm-button-text="{{ $review->is_approved === null ? 'Save changes' : 'Resubmit for review' }}"></div>

  <script type="application/json" id="ed-preloaded-translation">
  {!! $payload !!}
  </script>

@endsection

@section('scripts')
  <script type="text/javascript" src="/js/glaemscribe.js" async></script>
  <script type="text/javascript" src="/js/translation-admin.js" async></script>
@endsection
