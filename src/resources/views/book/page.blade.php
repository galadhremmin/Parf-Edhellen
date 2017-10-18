@extends('_layouts.default')

@section('title', ucfirst($payload['word']))

@section('body')
<div id="ed-book-for-bots">
@include('book._page', $payload)
</div>
<script type="application/json" id="ed-preloaded-book">
{!! json_encode($payload) !!}
</script>
@endsection

@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/comment.js)" async></script>
@endsection
