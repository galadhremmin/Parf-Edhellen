@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Welcome!')
@section('body')

<header>
  <h1 class="fst-italic" title="Well met!">Mae govannen!</h1>
  <p class="text-center">
    Well met! You have found an elvish book, <em>Parf Edhellen</em>, dedicated
    to the fictional languages in Tolkien's legendarium. 
  </p>
  <hr>
</header>
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
      The dictionary contains <strong>{{ $noOfWords }}</strong> words, 
      <strong>{{ $noOfLexicalEntries }}</strong> active glosses and
      <strong>{{ $noOfSentences }}</strong> phrases.
      The community has posted <strong>{{ $noOfPosts }}</strong> comments, 
      finished <strong>{{ $noOfFlashcards }}</strong> flashcards and 
      given <strong>{{ $noOfThanks }}</strong> thanks. You can access more
      statistics by going to <a href="{{ route('discuss.members') }}">Contributors</a>.
    </p>
    <p>
      <em>Parf Edhellen</em> is and has been open source since its inception {{ date('Y') - 2011 }} years ago. It is 
      developed and maintained by Leonard (<a href="https://twitter.com/parmaeldo" target="_blank">@parmaeldo</a>).
      If you are a developer, you can follow the project on <a href="https://github.com/galadhremmin/Parf-Edhellen" target="_blank">Github</a>.
    </p>
    @include('_shared._ad', [
      'ad' => 'frontpage'
    ])
  </div>
  <div class="col-xs-12 col-sm-6 col-md-4">
    @if ($lexicalEntry)
    <h4>Gloss of the hour</h4>
    <div class="hourly-gloss">
      @include('book._lexical-entry', [
        'lexicalEntry' => $lexicalEntry,
        'hideComments' => true
      ])
      <p class="text-end">
        <a href="{{ $link->lexicalEntry($lexicalEntry->id) }}" class="btn btn-sm btn-secondary">
          Learn more
        </a>
      </p>
    </div>
    @endif
    @if ($sentence)
    <hr class="d-sm-none d-md-block" />
    <h4>Phrase of the day</h4>
    @include('sentence._random', [ 
      'sentence' => $sentence
    ])
    @endif
  </div>
  <div class="col-xs-12 col-sm-12 col-md-4">
    <hr class="d-md-none" />
    <h4>Community activity</h4>
    <p>
      The {{count($auditTrails)}} most recent activities.
    </p>
    @include('_shared._audit-trail', [
      'auditTrail' => $auditTrails
    ])
  </div>
</div>
@include('_shared._ad', [
  'ad' => 'frontpage'
])

@endsection
