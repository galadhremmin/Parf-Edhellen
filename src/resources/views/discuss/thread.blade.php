@extends('_layouts.default')

@section('title', $thread->subject.' - Discussion')
@section('body')
  <article>
    <nav class="discuss-breadcrumbs">
      {!! Breadcrumbs::render('discuss.show', $group, $thread) !!}
    </nav>
    <header>
      <h1 class="mb-3">{{ $thread->subject }}</h1>
    </header>
    @if ($thread->entity_type !== 'discussion')
    <section class="discuss-entity">
    {!! $context->view($thread->entity) !!}
    </section>
    @endif
    <section class="discuss-body"
             data-inject-module="discuss"
             data-inject-prop-highlight-thread-post="true"
             data-inject-prop-current-page="@json($preloadedPosts['current_page'])"
             data-inject-prop-no-of-pages="@json($preloadedPosts['no_of_pages'])"
             data-inject-prop-thread-post-id="@json($preloadedPosts['thread_post_id'])"
             data-inject-prop-jump-post-id="@json($preloadedPosts['jump_post_id'])"
             data-inject-prop-no-of-posts="@json($preloadedPosts['no_of_posts'])"
             data-inject-prop-pages="@json($preloadedPosts['pages'])"
             data-inject-prop-posts="@json($preloadedPosts['posts'])"
             data-inject-prop-thread="@json($thread)">
      @include('_shared._loading-noscript')
      @foreach ($preloadedPosts['posts'] as $post)
        @include('discuss._post', $post)
      @endforeach
      @include('discuss._pagination', $preloadedPosts)
    </section>
    <section>
    @include('_shared._ad', [
      'ad' => 'forum'
    ])
    </section>
  </article>
@endsection
