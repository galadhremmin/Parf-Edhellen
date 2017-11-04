@extends('_layouts.default')

@section('title', 'Contribute')
@section('body')
  <h1>Contribute</h1>
  
  {!! Breadcrumbs::render('contribution.create', 'gloss') !!}
  <div id="ed-gloss-form" data-admin="false" data-confirm-button-text="{{ isset($payload) ? 'Propose changes' : 'Submit for review' }}"></div>

  @if (isset($payload))
  <script type="application/json" id="ed-preloaded-gloss">{!! $payload !!}</script>
  @endif

@endsection

@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/gloss-admin.js)" async></script>
@endsection
