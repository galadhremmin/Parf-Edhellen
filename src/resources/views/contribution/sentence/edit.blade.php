@extends('_layouts.default')

@section('title', 'Change contribution')
@section('body')
  <h1>Change contribution #{{ $review->id }}</h1>
  
  {!! Breadcrumbs::render('contribution.edit', $review->id) !!}
  <div id="ed-sentence-form" data-admin="false"></div>

  <script type="application/json" id="ed-preloaded-sentence">
  {!! $sentence !!}
  </script>
  <script type="application/json" id="ed-preloaded-sentence-fragments">
  {!! $fragmentData !!}
  </script>

@endsection

@section('scripts')
  <script type="text/javascript" src="/js/glaemscribe.js" async></script>
  <script type="text/javascript" src="/js/sentence-admin.js" async></script>
@endsection