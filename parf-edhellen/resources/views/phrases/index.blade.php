@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Phrases')
@section('body')

  <h1>Phrases <span class="tengwar" aria-hidden="true">zF4$jR6</span></h1>
  <p>
    Studying attested phrases is a great way of learn Tolkien's languages.
    We currently have {{ $numberOfSentences }} phrases in our database, and
    {{ $numberOfNeologisms }} of them are neologisms.
  </p>
  <div class="row">
    <div class="col-sm-6">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">Languages</h2>
        </div>
        <div class="panel-body">
          <ul>
          @foreach ($languages as $language)
            <li><a href="{{ $link->phraseByLanguage($language->ID, $language->Name) }}">{{ $language->Name }}</a></li>
          @endforeach
          </ul>
        </div>
      </div>
    </div>
    <div class="col-sm-6">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">Recent submissions</h2>
        </div>
        <div class="panel-body">
          <ul>
            @foreach ($languages as $language)
            <li>{{ $language->Name }}</li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
  </div>
@endsection