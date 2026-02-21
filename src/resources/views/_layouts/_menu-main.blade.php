<div class="container">
  <a class="navbar-brand" href="/">{{ config('ed.title') }}</a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-menu-content" aria-controls="main-menu-content" aria-expanded="false" aria-label="Toggle navigation">
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
  </button>
  <div class="navbar-collapse" id="main-menu-content">
    <ul class="navbar-nav me-auto">
      <li class="nav-item">
        <a class="nav-link {{ active('home') }}" href="/">@lang('home.title')</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ active('about') }}" href="{{ route('about') }}">@lang('about.title')</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ active(['sentence.public', 'sentence.public.language', 'sentence.public.sentence']) }}" href="{{ route('sentence.public') }}">@lang('sentence.title')</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ active(['games', 'flashcard', 'flashcard.cards', 'word-finder.index', 'word-finder.show']) }}" href="{{ route('games') }}">@lang('games.title')</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ active(['discuss.index', 'discuss.group', 'discuss.show', 'discuss.member-list']) }}" href="{{ route('discuss.index') }}">
          @lang('discuss.title')
        </a>
      </li>
    </ul>
    <ul class="navbar-nav ms-auto">
      @include('_layouts._menu-admin-dropdown')
      @include('_layouts._menu-theme-toggle')
      @include('_layouts._menu-user-dropdown', [
        'storage' => $storage
      ])
    </ul>
  </div>
</div>
