@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Welcome!')
@section('body')
  <h1>Dashboard</h1>
  
  {!! Breadcrumbs::render('dashboard') !!}

  <div class="alert alert-info">
    <strong>Hi!</strong>
    I just want to let you know that this is a <em>very</em> early version of the dashboard.
    I am still working on it! 
  </div>

  <div class="row">
    <div class="col-md-6">

      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title"><span class="glyphicon glyphicon-user"></span> About you</h2>
        </div>
        <div class="panel-body">
          <ul class="pill-nav">
            <li>
              <a href="{{ route('author.my-profile') }}">
                <span class="glyphicon glyphicon-user"></span>
                Your profile
              </a>
            </li>
          </ul>
        </div>
      </div>

      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title"><span class="glyphicon glyphicon-globe"></span> Community</h2>
        </div>
        <div class="panel-body">
          <ul class="pill-nav">
            <li>
              <a href="{{ route('flashcard') }}">
                <span class="glyphicon glyphicon-tags"></span>
                Flashcards

                @if ($noOfFlashcards)
                <span class="label label-info">{{ $noOfFlashcards }}</span>
                @endif
              </a>
            </li>
            <li>
              <a href="{{ route('translation-review.index') }}">
              <span class="glyphicon glyphicon-globe"></span>

                Contributions
                @if ($noOfContributions)
                <span class="label label-info">{{ $noOfContributions }}</span>
                @endif
              </a>
            </li>
          </ul>
        </div>
      </div>

    </div>
    <div class="col-md-6">
      @if ($user->isAdministrator())
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title"><span class="glyphicon glyphicon-cog"></span> Administration</h2>
        </div>
        <div class="panel-body">
          <ul>
            <li>
              <a href="{{ route('translation-review.list') }}">Contributions</a>
              @if ($noOfPendingContributions > 0)
              <span class="label label-info">{{ $noOfPendingContributions }}</span>
              @endif
            </li>
            <li><a href="{{ route('inflection.index') }}">Inflections</a></li>
            <li><a href="{{ route('speech.index') }}">Type of speeches</a></li>
            <li><a href="{{ route('sentence.index') }}">Phrases</a></li>
            <li><a href="{{ route('translation.index') }}">Words</a></li>
          </ul>
          <hr>
          <ul>
            <li><a href="{{ route('system-error.index') }}">System errors</a></li>
          </ul>
        </div>
      </div>
      @endif
    </div>

  </div>
@endsection
