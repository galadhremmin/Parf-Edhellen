@extends('_layouts.default')

@section('title', 'Add sentence - Administration')
@section('body')

<h1>Phrases</h1>
{!! Breadcrumbs::render('sentence.create') !!}

<div id="ed-sentence-form"></div>
<script type="application/json" id="ed-sentence-languages">
{{ $languages }}
</script>

@endsection

@section('scripts')
  <script type="text/javascript" src="/js/sentence-admin.js" async></script>
@endsection