@extends('_layouts.default')

@section('title', ucfirst($payload['word']))

@section('body')
<script type="application/json" id="ed-preloaded-book">
{!! json_encode($payload) !!}
</script>
@endsection

@section('scripts')
  <script type="text/javascript" src="/js/comment.js" async></script>
@endsection
