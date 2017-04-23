@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Welcome!')
@section('body')

<div class="jumbotron">
  <h1>Mae govannen!</h1>
  <p>
    Parf Edhellen&mdash;an <em>Elvish Book</em>&mdash;is a free online dictionary for Tolkien's languages.
    It consists of glosses imported from <em>Ardalambion</em>, <em>Eldamo</em>, <em>Hiswelókë</em> and
    <a href="{{ route('about') }}">many others</a>.</p>
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
        <a href="{{ $link->translation($review->TranslationID) }}">{{ $review->Word }}</a>
        by <a href="{{ $link->author($review->AuthorID, $review->AuthorName) }}">{{ $review->AuthorName }}</a>
      </li>
    @endforeach
    </ul>
    <hr class="visible-xs">
  </div>
  <div class="col-xs-12 col-sm-4">
    <h4>Random phrase</h4>
    @include('sentence.public._random', [ 'sentence' => $sentence ])
  </div>
</div>

@endsection