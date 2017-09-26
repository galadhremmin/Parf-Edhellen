@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Welcome!')
@section('body')

@if ($background)
<div class="jumbotron" style="background-image:url(img/jumbotron/{{ $background }}">
@else
<div class="jumbotron">
@endif
  <h1 title="Well met!">Mae govannen!</h1>
  <p>
    This is an Elvish Book, <em>Parf Edhellen</em>, dedicated to Tolkien's languages.
</div>

<hr>

<div class="row">
  <div class="col-xs-12 col-sm-6 col-md-4">
    <h4>About</h4>
    <p>
      This website is dedicated to Tolkien's languages, with an emphasis on 
      the elvish languages of his legendarium. Our dictionary consists of
      imported glosses from a variety of quality dictionaries. You can read 
      more <a href="{{ route('about') }}">on our about page</a>.
    </p>
    <p>
      <em>Parf Edhellen</em> is a non-profit, non-commercial endeavor. It is 
      developed and maintained by Leonard. Please contact us on Twitter if you 
      would like to get in touch,
      <a href="https://twitter.com/parmaeldo" target="_blank">@parmaeldo</a>.
      If you are a developer, you can also follow the project on 
      <a href="https://github.com/galadhremmin/Parf-Edhellen" target="_blank">Github</a>.
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
    <ul class="list-group">
    @foreach($auditTrails as $a)
      <li class="list-group-item">
        {{ $a['created_at'] }}
        <a href="{{ $link->author($a['account_id'], $a['account_name']) }}">{{ $a['account_name'] }}</a>
        {!! $a['message'] . ($a['entity'] === null ? '.' : ' '. $a['entity'].'.') !!}
      </li>
    @endforeach
    </ul>
  </div>
</div>

@endsection
