@extends('_layouts.default')

@section('title', $author ? $author->nickname : 'Missing account')

@section('body')
  @if ($author === null)
    This is not the droid you are looking for.
  @else
    <header class="profile-header">
      <div class="ed-profile-picture" {!! $avatar ? 'style="background-image:url('.$avatar.')"' : '' !!}></div>
      @if (!empty($author->tengwar))
      <span aria-hidden="true" class="tengwar">{{ $author->tengwar }}</span>
      @endif
      <h1>
        {{ $author->nickname }}
      </h1>
    </header>

    <div class="row">
      <div class="col-md-8 col-sm-12">
      @if (!empty($profile))
        {!! $profile !!}
      @else
        <p>
          {{ $author->nickname }} is but a rumour in the wind. Perhaps one day they might
          come forth and reveal themselves.
        </p>
      @endif

      @if (Auth::check() && Auth::user()->id === $author->id)
        <div class="text-right">
          <a href="{{ route('author.edit-profile') }}" class="btn btn-default">
            <span class="glyphicon glyphicon-edit"></span>
            Edit profile
          </a>
        </div>
      @endif
      </div>
      <div class="col-md-4 col-sm-12">
        <h2 class="hidden-md hidden-lg">Statistics</h2>
        <table class="table striped">
          <tbody>
            <tr>
              <th>Words</th>
              <td class="text-right">{{ $stats['noOfWords'] }}</td>
            </tr>
            <tr>
              <th><a href="{{ route('author.translations', ['id' => $author->id]) }}">Glosses</a></th>
              <td class="text-right">{{ $stats['noOfTranslations'] }}</td>
            </tr>
            <tr>
              <th><a href="{{ route('author.sentences', ['id' => $author->id]) }}">Phrases</a></th>
              <td class="text-right">{{ $stats['noOfSentences'] }}</td>
            </tr>
            <tr>
              <th><a href="{{ route('author.posts', ['id' => $author->id]) }}">Posts</a></th>
              <td class="text-right">{{ $stats['noOfPosts'] }}</td>
            </tr>
            <tr>
              <th><span class="glyphicon glyphicon-thumbs-up"></span> Thanks</th>
              <td class="text-right">{{ $stats['noOfThanks'] }}</td>
            </tr>
            <tr>
              <th><span class="glyphicon glyphicon-tag"></span> Flashcards</th>
              <td class="text-right">{{ $stats['noOfFlashcards'] }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  @endif
  <hr>
  @include('_shared._comments', [
    'entity_id' => $author->id,
    'morph'     => 'account',
    'enabled'   => true
  ])
@endsection

@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/comment.js)" async></script>
@endsection
