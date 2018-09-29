@extends('_layouts.default')

@section('title', $thread->subject.' - Discussion')
@section('body')
  <article>
    <nav class="discuss-breadcrumbs">
      {!! Breadcrumbs::render('discuss.show', $thread) !!}
    </nav>
    <header>
      <h1>{{ $thread->subject }}</h1>
    </header>
    <section class="discuss-entity">
    {!! $context->view($thread->entity) !!}
    </section>
    <aside id="discuss-toolbar" data-thread-id="{{ $thread->id }}"></aside>
    <section class="discuss-body">
      @include('_shared._comments', [
        'entity_id' => $thread->entity_id,
        'morph'     => $thread->entity_type,
        'enabled'   => true,
        'order'     => 'asc'
      ])
    </section>
  </article>
@endsection
@section('styles')
<link rel="stylesheet" href="@assetpath(css/app.discuss.css)">
@endsection
@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/comment.js)" async></script>
  <script type="text/javascript" src="@assetpath(/js/discuss-tools.js)" async></script>
@endsection
