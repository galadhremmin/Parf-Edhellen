@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Welcome '.$user->nickname.'!')
@section('body')
  <h1>Dashboard</h1>
  
  {!! Breadcrumbs::render('dashboard') !!}

  <div class="row">
    <div class="col-md-6">

      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title"><span class="glyphicon glyphicon-user"></span> About &ldquo;{{ $user->nickname }}&rdquo;</h2>
        </div>
        <div class="panel-body">
          <ul class="dashboard-link-list">
            <li>
              <a href="{{ route('author.my-profile') }}">
                <span class="glyphicon glyphicon-user"></span>
                Your profile
              </a>
            </li>
              <li>
                <a href="{{ route('mail-setting.index') }}">
                  <span class="glyphicon glyphicon-bell"></span>
                  Mail notifications
                </a>
              </li>
            @if ($user->isAdministrator())
              @if ($incognito)
              <li>
                <a href="{{ route('dashboard.incognito', ['incognito' => false]) }}">
                  <span class="glyphicon glyphicon-eye-open"></span>
                  Be visible
                </a>
              </li>
              @else
              <li>
                <a href="{{ route('dashboard.incognito', ['incognito' => true]) }}">
                  <span class="glyphicon glyphicon-eye-close"></span>
                  Go incognito
                </a>
              </li>
              @endif
            @endif
          </ul>
        </div>
      </div>

      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title"><span class="glyphicon glyphicon-globe"></span> Community</h2>
        </div>
        <div class="panel-body">
          <ul class="dashboard-link-list">
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
              <a href="{{ route('contribution.index') }}">
                <span class="glyphicon glyphicon-book"></span>
                Contributions
                @if ($noOfContributions)
                <span class="label label-info">{{ $noOfContributions }}</span>
                @endif
              </a>
            </li>
            <li>
              <a href="{{ route('author.posts', ['id' => $user->id]) }}">
              <span class="glyphicon glyphicon-comment"></span>
                Comments
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
              <a href="{{ route('contribution.list') }}">Contributions</a>
              @if ($noOfPendingContributions > 0)
              <span class="label label-info">{{ $noOfPendingContributions }}</span>
              @endif
            </li>
            <li><a href="{{ route('inflection.index') }}">Inflections</a></li>
            <li><a href="{{ route('speech.index') }}">Type of speeches</a></li>
            <li><a href="{{ route('sentence.index') }}">Phrases</a></li>
            <li><a href="{{ route('gloss.index') }}">Glossary</a></li>
          </ul>
          <hr>
          <ul>
            <li><a href="{{ route('account.index') }}">Accounts</a></li>
            <li>
              <a href="{{ route('system-error.index') }}">System errors</a> 
              <span class="label label-info">{{ $numberOfErrors }}</span>
            </li>
          </ul>
        </div>
      </div>
      @endif
    </div>

  </div>
@endsection
@section('styles')
<link rel="stylesheet" href="@assetpath(style-dashboard.css)">
@endsection
