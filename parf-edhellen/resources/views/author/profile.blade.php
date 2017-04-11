@extends('_layouts.default')

@section('title', $author ? $author->Nickname : 'Missing account')

@section('body')
  @if ($author === null)
    This is not the droid you are looking for.
  @else
    <header class="clearfix">
      <div class="ed-profile-picture"></div>
      <h1>
        {{ $author->Nickname }}
      </h1>
      @if (!empty($author->Tengwar))
      <h2 class="tengwar">{{ $author->Tengwar }}</h2>
      @endif
    </header>

    <div class="row">
      <div class="col-md-8 col-sm-12">
      @if (!empty($profile))
        {!! $profile !!}
      @else
        <p>
          {{ $author->Nickname }} is but a rumour in the wind. Perhaps one day they might
          come forth and reveal themselves.
        </p>
      @endif

      @if (Auth::user()->AccountID === $author->AccountID)
        <a href="{{ route('author.edit-profile') }}" class="btn btn-primary">Edit profile</a>
      @endif
      </div>
      <div class="col-md-4 col-sm-12">
        <h2>Statistics</h2>
        <table class="table striped">
          <tbody>
            <tr>
              <th>Words</th>
              <td>{{ $stats['noOfWords'] }}</td>
            </tr>
            <tr>
              <th>Translations</th>
              <td>{{ $stats['noOfTranslations'] }}</td>
            </tr>
            <tr>
              <th>Sentences</th>
              <td>{{ $stats['noOfSentences'] }}</td>
            </tr>
            <tr>
              <th><span class="glyphicon glyphicon-thumbs-up"></span> Thanks</th>
              <td>{{ $stats['noOfThanks'] }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  @endif
@endsection
