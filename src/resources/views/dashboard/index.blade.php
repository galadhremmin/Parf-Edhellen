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
          <h2 class="panel-title"><span class="TextIcon TextIcon--person"></span> About &ldquo;{{ $user->nickname }}&rdquo;</h2>
        </div>
        <div class="panel-body">
          <ul class="dashboard-link-list">
            <li>
              <a href="{{ route('author.my-profile') }}">
                <span class="TextIcon TextIcon--person"></span>
                Your profile
              </a>
            </li>
              <li>
                <a href="{{ route('mail-setting.index') }}">
                  <span class="TextIcon TextIcon--bell"></span>
                  Mail notifications
                </a>
              </li>
            @if ($user->isAdministrator())
              @if ($incognito)
              <li>
                <a href="{{ route('dashboard.incognito', ['incognito' => false]) }}">
                  Be visible
                </a>
              </li>
              @else
              <li>
                <a href="{{ route('dashboard.incognito', ['incognito' => true]) }}">
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
          <h2 class="panel-title"><span class="TextIcon TextIcon--people"></span> Community</h2>
        </div>
        <div class="panel-body">
          <ul class="dashboard-link-list">
            <li>
              <a href="{{ route('flashcard') }}">
                🗃
                Flashcards

                @if ($noOfFlashcards)
                <span class="badge bg-secondary">{{ $noOfFlashcards }}</span>
                @endif
              </a>
            </li>
            <li>
              <a href="{{ route('contribution.index') }}">
                <span class="TextIcon TextIcon--book"></span>
                Contributions
                @if ($noOfContributions)
                <span class="badge bg-secondary">{{ $noOfContributions }}</span>
                @endif
              </a>
            </li>
            <li>
              <a href="{{ route('author.posts', ['id' => $user->id]) }}">
              <span class="TextIcon TextIcon--comment"></span>
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
          <h2 class="panel-title">Administration</h2>
        </div>
        <div class="panel-body">
          <ul class="dashboard-link-list no-icon">
            <li>
              <a href="{{ route('contribution.list') }}">
                Contributions
                @if ($noOfPendingContributions > 0)
                <span class="badge bg-secondary">{{ $noOfPendingContributions }}</span>
                @endif
              </a>
            </li>
            <li><a href="{{ route('inflection.index') }}">Inflections</a></li>
            <li><a href="{{ route('speech.index') }}">Type of speeches</a></li>
            <li><a href="{{ route('sentence.index') }}">Phrases</a></li>
            <li><a href="{{ route('gloss.index') }}">Glossary</a></li>
            <li><a href="{{ route('account.index') }}">Accounts</a></li>
            <li><a href="{{ route('word-finder.config.index') }}">Sage configuration</a></li>
            <li>
              <a href="{{ route('system-error.index') }}">
                System errors 
                <span class="badge bg-secondary">{{ $numberOfErrors }}</span>
              </a>
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
