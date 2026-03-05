@extends('_layouts.default')

@section('title', ucfirst($payload['word']))
@section('description', __('glossary.description', ['word' => ucfirst($payload['word'])]))
@section('og_type', 'article')

@section('body')
<div id="ed-book-for-bots">
@include('book.'.$payload['group_intl_name'].'.index', $payload)
</div>
<script type="application/json" id="ed-preloaded-book">
{!! json_encode($payload) !!}
</script>
@endsection
