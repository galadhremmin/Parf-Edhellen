<div class="d-lg-none d-xl-none">
@if (auth()->check())

@if (auth()->user()->isAdministrator() || auth()->user()->memberOf(\App\Security\RoleConstants::Reviewers))
<ul class="navbar-nav">
  <li class="nav-item">
    <a class="nav-link {{ active('admin.contribution.list') }}" href="{{ route('admin.contribution.list') }}">
      Contributions
    </a>
  </li>
</ul>
@endif

@if (auth()->user()->isAdministrator())
<ul class="navbar-nav">
  <li class="nav-item">
    <a class="nav-link {{ active('inflection.index') }}" href="{{ route('inflection.index') }}">Inflections</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ active('speech.index') }}" href="{{ route('speech.index') }}">Type of speech</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ active('sentence.index') }}" href="{{ route('sentence.index') }}">Phrases</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ active('gloss.index') }}" href="{{ route('gloss.index') }}">Glossary</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ active('account.index') }}" href="{{ route('account.index') }}">Accounts</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ active('word-finder.index') }}" href="{{ route('word-finder.config.index') }}">Sage configuration</a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ active('system-error.index') }}" href="{{ route('system-error.index') }}">
      System errors
    </a>
  </li>
</ul>
@endif
<a class="avatar-in-menu {{ active('author.my-profile') }}" href="{{ route('author.my-profile') }}">
  <ins class="avatar-in-menu" style="background-image:url({{ $storage->accountAvatar(auth()->user(), true) }})" role="img"></ins>
  <span>{{ auth()->user()->nickname }}</span>
</a>
<ul class="navbar-nav">
  <li class="nav-item">
    <a class="nav-link {{ active('author.my-profile') }}" href="{{ route('author.my-profile') }}">
      @lang('community.profile')
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ active('contribution.index') }}" href="{{ route('contribution.index') }}">
      @lang('community.contributions')
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ active('notifications.index') }}" href="{{ route('notifications.index') }}">
      @lang('community.notification-settings')
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ active('account.security') }}" href="{{ route('account.security') }}">
      @lang('community.security')
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ active('discuss.members') }}" href="{{ route('discuss.members') }}">
      @lang('discuss.member-list.title')
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="{{ route('logout') }}">
      @lang('community.logout')
    </a>
  </li>
</ul>
@else
<a class="avatar-in-menu" href="{{ route('login') }}">
  <ins class="avatar-in-menu" style="background-image:url({{ $storage->accountAvatar(null, true) }})" role="img"></ins>
  <span>Guest</span>
</a>
<ul class="navbar-nav">
  <li class="nav-item">
    <a class="nav-link {{ active('login') }}" href="{{ route('login') }}">
      @lang('community.login')
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link {{ active('register') }}"  href="{{ route('register') }}">
      @lang('community.register')
    </a>
  </li>
</ul>
@endif
</div>