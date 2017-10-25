@extends('_layouts.default')

@section('title', $thread->subject.' - Discussion')
@section('body')
  <h1>{{ $thread->subject }}</h1>
  
  {!! Breadcrumbs::render('discuss.show', $thread) !!}
  
  {!! $context->view($thread->entity) !!}
  <hr>
  @include('_shared._comments', [
    'entity_id' => $thread->entity_id,
    'morph'     => $thread->entity_type,
    'enabled'   => true,
    'order'     => 'asc'
  ])

@endsection
@section('styles')
<link rel="stylesheet" href="@assetpath(css/app.discuss.css)">
@endsection
@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/comment.js)" async></script>
@endsection
