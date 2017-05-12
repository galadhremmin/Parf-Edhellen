@extends('_layouts.default')

@section('title', 'Add word - Administration')
@section('body')

<h1>Add word</h1>
{!! Breadcrumbs::render('translation.create') !!}

<div id="ed-translation-form"></div>

@endsection

@section('scripts')
  <script type="text/javascript" src="/js/glaemscribe.js" async></script>
  <script type="text/javascript" src="/js/translation-admin.js" async></script>
@endsection
