@extends('_layouts.default')

@section('title', ucfirst($word))

@section('body')
<div class="ed-remove-when-navigating">
  @include('book._page', [
    'sections' => $sections,
    'single'   => $single
  ])
</div>
@endsection

@section('scripts')
  <script type="text/javascript" src="/js/comment.js" async></script>
@endsection