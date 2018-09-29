@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Welcome!')
@section('body')

<div class="introtron">
  <h1 title="Well met!">Mae govannen!</h1>
  <p>
    Well met! You have found an elvish book, <em>Parf Edhellen</em>, dedicated
    to the fictional languages in Tolkien's legendarium. 
  </p>
</div>
<div class="row">
  <div class="col-xs-12 col-sm-6 col-md-4">
    <h4>About the website</h4>
    <p>
      This website is dedicated to Tolkien's languages, with an emphasis on 
      the elvish languages of his legendarium. Our dictionary consists of
      imported glosses from a variety of quality dictionaries, categorised
      and searchable by sense, conjugation and more. You can read 
      more <a href="{{ route('about') }}">on our about page</a>.
    </p>
    <p>
      <em>Parf Edhellen</em> is entirely open source. It is 
      developed and maintained by Leonard (<a href="https://twitter.com/parmaeldo" target="_blank">@parmaeldo</a>).
      If you are a developer, you can follow the project on <a href="https://github.com/galadhremmin/Parf-Edhellen" target="_blank">Github</a>.
    </p>
    <hr class="visible-xs">
  </div>
  @if ($sentence)
  <div class="col-xs-12 col-sm-6 col-md-4">
    <h4>Random phrase</h4>
    @include('sentence.public._random', [ 
      'sentence'     => $sentence,
      'sentenceData' => $sentenceData
    ])
  </div>
  @endif
  <hr class="hidden-md hidden-lg clear-left">
  <div class="col-xs-12 col-sm-6 col-md-4">
    <h4>Community activity</h4>
    <p>
      The {{count($auditTrails)}} most recent activities.
    </p>
    @include('_shared._audit-trail', [
      'auditTrail' => $auditTrails
    ])
  </div>
</div>

@endsection
