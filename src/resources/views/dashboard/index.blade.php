@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Welcome '.$user->nickname.'!')
@section('body')
<h1>@lang('dashboard.title')</h1>

{!! Breadcrumbs::render('dashboard') !!}

<div class="container">
  <div class="row row-cols-1 row-cols-sm-2">
    <section class="col">
      <div class="card mb-3">
        <div class="card-body shadow"
             id="ed-profile-aside"
             data-inject-module="dashboard-profile"
             data-inject-prop-container="Profile"
             data-inject-prop-account="@json($user)"
             data-inject-prop-hide-profile="true"
             data-inject-prop-readonly="false"></div>
      </div>
      <div class="text-muted mb-3 mb-sm-0">
        Last modified: @date($user->updated_at)
      </div>
    </section>
    <section class="col">
      <div class="list-group mb-3">
        <a href="{{ route('author.my-profile') }}" class="list-group-item list-group-item-action">
          Your profile
        </a>
        <a href="{{ route('flashcard') }}" class="list-group-item list-group-item-action">
          Flashcards

          @if ($noOfFlashcards)
          <span class="badge bg-secondary float-end">{{ $noOfFlashcards }}</span>
          @endif
        </a>
        <a href="{{ route('contribution.index') }}" class="list-group-item list-group-item-action">
          Contributions
          @if ($noOfContributions)
          <span class="badge bg-secondary float-end">{{ $noOfContributions }}</span>
          @endif
        </a>
        <a href="{{ route('author.posts', ['id' => $user->id]) }}" class="list-group-item list-group-item-action">
          Comments
        </a>
        <a href="{{ route('mail-setting.index') }}" class="list-group-item list-group-item-action">
          Privacy settings
        </a>
      </div>
      @if ($user->isAdministrator())
      <div class="list-group">
        @if ($user->isAdministrator())
        @if ($incognito)
        <a href="{{ route('dashboard.incognito', ['incognito' => false]) }}" class="list-group-item list-group-item-action">
          Be visible
        </a>
        @else
        <a href="{{ route('dashboard.incognito', ['incognito' => true]) }}" class="list-group-item list-group-item-action">
          Go incognito
        </a>
        @endif
        @endif
        <a href="{{ route('contribution.list') }}" class="list-group-item list-group-item-action">
          Contributions
          @if ($noOfPendingContributions > 0)
          <span class="badge bg-secondary float-end">{{ $noOfPendingContributions }}</span>
          @endif
        </a>
        <a href="{{ route('inflection.index') }}" class="list-group-item list-group-item-action">Inflections</a>
        <a href="{{ route('speech.index') }}" class="list-group-item list-group-item-action">Type of speeches</a>
        <a href="{{ route('sentence.index') }}" class="list-group-item list-group-item-action">Phrases</a>
        <a href="{{ route('gloss.index') }}" class="list-group-item list-group-item-action">Glossary</a>
        <a href="{{ route('account.index') }}" class="list-group-item list-group-item-action">Accounts</a>
        <a href="{{ route('word-finder.config.index') }}" class="list-group-item list-group-item-action">Sage configuration</a>
        <a href="{{ route('system-error.index') }}" class="list-group-item list-group-item-action">
          System errors 
          <span class="badge bg-secondary float-end">{{ $numberOfErrors }}</span>
        </a>
      </div>
      @endif
    </section>
  </div>
</div>
@endsection
@section('styles')
<link rel="stylesheet" href="@assetpath(style-dashboard.css)">
@endsection
