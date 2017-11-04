@extends('_layouts.default')

@section('title', 'Contribute')
@section('body')
  <h1>Contribute</h1>
  
  {!! Breadcrumbs::render('contribution.create', 'sentence') !!}
  <div id="ed-sentence-form" data-admin="false"></div>

  @if (isset($sentence) && isset($fragmentData))
  <script type="application/json" id="ed-preloaded-sentence">{!! $sentence !!}</script>
  <script type="application/json" id="ed-preloaded-sentence-fragments">{!! $fragmentData !!}</script>
  @endif

@endsection

@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/sentence-admin.js)" async></script>
@endsection
