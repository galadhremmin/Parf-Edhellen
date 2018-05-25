@extends('_layouts.default')

@section('title', 'Contributors - Discussion')
@section('body')
  <h1>Contributors</h1>
  
  {!! Breadcrumbs::render('discuss.members') !!}
  
  <h2>Most glosses</h2>
  @include('discuss._top-list', [
    'list' => $data['glosses'],
    'accounts' => $data['accounts'],
    'property' => 'glosses'
  ])

  <h2>Most phrases</h2>
  @include('discuss._top-list', [
    'list' => $data['sentences'],
    'accounts' => $data['accounts'],
    'property' => 'sentences'
  ])

  <h2>Top contributors</h2>
  @include('discuss._top-list', [
    'list' => $data['contributions'],
    'accounts' => $data['accounts'],
    'property' => 'contributions'
  ])

  <h2>Most posts</h2>
  @include('discuss._top-list', [
    'list' => $data['forum_posts'],
    'accounts' => $data['accounts'],
    'property' => 'forum_posts'
  ])

  <h2>Most likes</h2>
  @include('discuss._top-list', [
    'list' => $data['forum_post_likes'],
    'accounts' => $data['accounts'],
    'property' => 'forum_post_likes'
  ])

  <h2>Most flashcards</h2>
  @include('discuss._top-list', [
    'list' => $data['flashcard_results'],
    'accounts' => $data['accounts'],
    'property' => 'flashcard_results'
  ])

@endsection
@section('styles')
<link rel="stylesheet" href="@assetpath(css/app.discuss.css)">
@endsection
@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/comment.js)" async></script>
@endsection
