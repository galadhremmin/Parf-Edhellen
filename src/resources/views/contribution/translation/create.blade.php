@extends('_layouts.default')

@section('title', 'Contribute')
@section('body')
  <h1>Contribute</h1>
  
  {!! Breadcrumbs::render('contribution.create', 'translation') !!}
  <div id="ed-translation-form" data-admin="false" data-confirm-button-text="{{ isset($payload) ? 'Propose changes' : 'Submit for review' }}"></div>

  @if (isset($payload))
  <script type="application/json" id="ed-preloaded-translation">{!! $payload !!}</script>
  @endif

@endsection

@section('scripts')
  <script type="text/javascript" src="/js/glaemscribe.js" async></script>
  <script type="text/javascript" src="/js/translation-admin.js" async></script>
@endsection
