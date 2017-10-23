@extends('_layouts.default')

@section('title', 'Discussion')
@section('body')
  <h1>Discussion <span class="tengwar">3D7w#3F</span></h1>
  
  {!! Breadcrumbs::render('discuss') !!}

  <div class="discuss-table">
  @foreach ($threads as $thread)
    <div class="r">
      <div class="c">
        @include('discuss._avatar', ['account' => $thread->account])
      </div>
      <div class="c">
        <a href="{{ route('discuss.show', ['id' => $thread->id]) }}">{{ $thread->subject }}</a>
        <div class="pi">
          {{ $thread->account->nickname }}
          {{ ($thread->updated_at ?: $thread->created_at)->format('Y-m-d H:i') }}
        </div>
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
