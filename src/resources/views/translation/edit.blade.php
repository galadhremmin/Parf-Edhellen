@extends('_layouts.default')

@section('title', $translation->word->word.' - Administration')
@section('body')

<h1>Edit gloss</h1>
{!! Breadcrumbs::render('translation.edit', $translation) !!}

<div id="ed-translation-form"></div>

<script type="application/json" id="ed-preloaded-translation">
{!! $translation !!}
</script>

@endsection

@section('scripts')
  <script type="text/javascript" src="/js/glaemscribe.js" async></script>
  <script type="text/javascript" src="/js/translation-admin.js" async></script>
@endsection
