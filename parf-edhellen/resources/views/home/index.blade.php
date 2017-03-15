@extends('_layouts.default')

@section('title', 'Welcome!')
@section('body')

<div class="jumbotron">
  <h1>Mae govannen!</h1>
  <p>
    Parf Edhellen&mdash;an <em>Elvish Book</em>&mdash;is a free online dictionary for Tolkien's languages.
    It consists of glosses imported from <em>Ardalambion</em>, <em>Eldamo</em>, <em>Hiswelókë</em> and
    <a href="about.page?browseTo=wordlist">many others</a>.</p>
</div>

<hr>

<div class="row">
  <div class="col-xs-12 col-sm-4">
    <h4>About me</h4>
    <p>My name is Leonard and I develop and maintain this elvish dictionary.
      If you want to get in touch with me, please tweet me at
      <a href="https://twitter.com/parmaeldo" target="_blank">@parmaeldo on Twitter</a>.</p>
    <hr class="visible-xs">
  </div>
  <div class="col-xs-12 col-sm-4">
    <h4>Community activity</h4>
    <ul class="list-group">
    @foreach($reviews as $review)
      <li class="list-group-item">
        {{ $review->DateCreated->format('Y-m-d H:i') }}
        <a href="/wt/{{ $review->TranslationID }}">{{ $review->Word }}</a>
        by <a href="/profile.page?authorID={{ $review->AuthorID }}">{{ $review->AuthorName }}</a>
      </li>
    @endforeach
    </ul>
    <hr class="visible-xs">
  </div>
  <div class="col-xs-12 col-sm-4">
    <h4>Random elvishness</h4>
    <blockquote class="daily-sentence">
      <p class="tengwar">
      @foreach ($sentence->fragments as $fragment){{ ($fragment->isPunctuationOrWhitespace() ? '' : ' ') . $fragment->Tengwar }}@endforeach
      </p>
      <p>
        <em>
        @foreach ($sentence->fragments as $fragment){{ ($fragment->isPunctuationOrWhitespace() ? '' : ' ') . $fragment->Fragment }}@endforeach
        </em>
      </p>
      <p>{{$sentence->Description}}</p>
      <footer>{{$sentence->language->Name}} [{{$sentence->Source}}]</footer>
    </blockquote>
  </div>
</div>

@endsection