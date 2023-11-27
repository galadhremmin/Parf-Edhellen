<nav>
  @if ($user)
  <a class="avatar-in-menu {{ active('author.my-profile') }}" href="{{ route('author.my-profile') }}">
    <ins class="avatar-in-menu" style="background-image:url({{ $storage->accountAvatar($user, true) }})" role="img"></ins>
    <span>{{ $user->nickname }}</span>
  </a>
  <ul class="list-group mb-3">
    <li class="list-group-item">
      <a class="{{ active('author.my-profile') }}" href="{{ route('author.my-profile') }}">
        <span class="TextIcon TextIcon--person"></span> 
        &nbsp;@lang('community.profile')
      </a>
    </li>
    <li class="list-group-item">
      <a class="{{ active('contribution.index') }}" href="{{ route('contribution.index') }}">
        <span class="TextIcon TextIcon--book"></span> 
        &nbsp;@lang('community.contributions')
      </a>
    </li>
    <li class="list-group-item">
      <a class="{{ active('notifications.index') }}"  href="{{ route('notifications.index') }}">
        <span class="TextIcon TextIcon--bell"></span> 
        &nbsp;@lang('community.notification-settings')
      </a>
    </li>
    <li class="list-group-item">
      <a class="{{ active('account.privacy') }}"  href="{{ route('account.privacy') }}">
        <span class="TextIcon TextIcon--exclamation-sign"></span> 
        &nbsp;@lang('community.privacy')
      </a>
    </li>
  </ul>
  @if ($isAdmin)
  <ul class="list-group mb-3">
    <li class="list-group-item">
      <a class="{{ active('contribution.list') }}" href="{{ route('contribution.list') }}">
        Contributions
      </a>
    </li>
    <li class="list-group-item">
      <a class="{{ active('inflection.index') }}" href="{{ route('inflection.index') }}">Inflections</a>
    </li>
    <li class="list-group-item">
      <a class="{{ active('speech.index') }}" href="{{ route('speech.index') }}">Type of speech</a>
    </li>
    <li class="list-group-item">
      <a class="{{ active('sentence.index') }}" href="{{ route('sentence.index') }}">Phrases</a>
    </li>
    <li class="list-group-item">
      <a class="{{ active('gloss.index') }}" href="{{ route('gloss.index') }}">Glossary</a>
    </li>
    <li class="list-group-item">
      <a class="{{ active('account.index') }}" href="{{ route('account.index') }}">Accounts</a>
    </li>
    <li class="list-group-item">
      <a class="{{ active('word-finder.index') }}" href="{{ route('word-finder.config.index') }}">Sage configuration</a>
    </li>
    <li class="list-group-item">
      <a class="{{ active('system-error.index') }}" href="{{ route('system-error.index') }}">
        System errors
      </a>
    </li>
  </ul>
  @endif
  <ul class="list-group mb-3">
    <li class="list-group-item">
      <a class="{{ active('discuss.members') }}" href="{{ route('discuss.members') }}">
        <span class="TextIcon TextIcon--people"></span> 
        @lang('discuss.member-list.title')
      </a>
    </li>
  </ul>
  <ul class="list-group mb-3">
    <li class="list-group-item">
      <a href="{{ route('logout') }}">
        <span class="TextIcon TextIcon--logout"></span> 
        &nbsp;@lang('community.logout')
      </a>
    </li>
  </ul>
  @else
  <a class="avatar-in-menu {{ active('author.my-profile') }}" href="{{ route('author.my-profile') }}">
    <ins class="avatar-in-menu" style="background-image:url({{ $storage->accountAvatar(null, true) }})" role="img"></ins>
    <span>Guest</span>
  </a>
  <ul class="list-group mb-3">
    <li class="list-group-item">
      <a class="{{ active('login') }}" href="{{ route('login') }}">
        <span class="TextIcon TextIcon--login"></span> 
        &nbsp;@lang('community.login')
      </a>
    </li>
    <li class="list-group-item">
      <a class="{{ active('register') }}"  href="{{ route('register') }}">
        <span class="TextIcon TextIcon--person"></span> 
        &nbsp;@lang('community.register')
      </a>
    </li>
  </ul>
  @endif
</nav>
