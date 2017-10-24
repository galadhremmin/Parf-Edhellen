@extends('_layouts.default')

@section('title', 'Discussion')
@section('body')
  <h1>Discussion <span class="tengwar">3D7w#3F</span></h1>
  
  {!! Breadcrumbs::render('discuss') !!}

  <p>
    <span class="glyphicon glyphicon-info-sign"></span> This is an aggregated view of all 
    comments left by the members of our community. You are more than welcome to participate in
    the conversation!
  </p>

  <div class="discuss-table">
  @foreach ($threads as $thread)
    <div class="r">
      <div class="c">
        @include('discuss._avatar', ['account' => $thread->account])
      </div>
      <div class="c p2">
        <a href="{{ route('discuss.show', ['id' => $thread->id]) }}">{{ $thread->subject }}</a>
        <div class="pi">
          {{ $thread->account->nickname }}
          {{ ($thread->updated_at ?: $thread->created_at)->format('Y-m-d H:i') }}
        </div>
      </div>
      <div class="c text-right">
        {{ $thread->number_of_posts }} <span class="glyphicon glyphicon-comment"></span>
        {{ $thread->number_of_likes }} <span class="glyphicon glyphicon-thumbs-up"></span>
      </div>
    </div>
  @endforeach
  </div>

@endsection
@section('styles')
<link rel="stylesheet" href="@assetpath(css/app.discuss.css)">
@endsection
@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/comment.js)" async></script>
@endsection
