@if (auth()->check())
<li class="nav-item dropdown">
  <a class="nav-link ed-user-menu-trigger dropdown-toggle {{ active('author.my-profile') }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true" aria-label="@lang('community.profile')">
    <ins class="ed-user-menu-avatar" style="background-image:url({{ $storage->accountAvatar(auth()->user(), true) }})" role="img" aria-hidden="true"></ins>
    <span class="ed-user-menu-label">{{ auth()->user()->nickname }}</span>
  </a>
  <ul class="dropdown-menu dropdown-menu-end ed-user-menu" aria-label="@lang('community.profile')">
    <li>
      <a class="dropdown-item {{ active('author.my-profile') }}" href="{{ route('author.my-profile') }}">
        <span class="TextIcon TextIcon--person" aria-hidden="true"></span>
        @lang('community.profile')
      </a>
    </li>
    <li>
      <a class="dropdown-item {{ active('contribution.index') }}" href="{{ route('contribution.index') }}">
        <span class="TextIcon TextIcon--book" aria-hidden="true"></span>
        @lang('community.contributions')
      </a>
    </li>
    <li>
      <a class="dropdown-item {{ active('notifications.index') }}" href="{{ route('notifications.index') }}">
        <span class="TextIcon TextIcon--bell" aria-hidden="true"></span>
        @lang('community.notification-settings')
      </a>
    </li>
    <li>
      <a class="dropdown-item {{ active('account.security') }}" href="{{ route('account.security') }}">
        <span class="TextIcon TextIcon--exclamation-sign" aria-hidden="true"></span>
        @lang('community.security')
      </a>
    </li>
    <li>
      <a class="dropdown-item {{ active('discuss.members') }}" href="{{ route('discuss.members') }}">
        <span class="TextIcon TextIcon--people" aria-hidden="true"></span>
        @lang('discuss.member-list.title')
      </a>
    </li>
    <li><hr class="dropdown-divider"></li>
    <li>
      <a class="dropdown-item" href="{{ route('logout') }}">
        <span class="TextIcon TextIcon--logout" aria-hidden="true"></span>
        @lang('community.logout')
      </a>
    </li>
  </ul>
</li>
@else
<li class="nav-item">
  <a class="nav-link ed-user-menu-trigger ed-user-menu-trigger--guest" href="{{ route('login') }}" aria-label="@lang('community.login')">
    <ins class="ed-user-menu-avatar" style="background-image:url({{ $storage->accountAvatar(null, true) }})" role="img" aria-hidden="true"></ins>
    <span class="ed-user-menu-label">Log in</span>
  </a>
</li>
@endif
