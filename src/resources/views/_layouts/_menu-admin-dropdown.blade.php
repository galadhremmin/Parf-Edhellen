@if (auth()->check() && (auth()->user()->isAdministrator() || auth()->user()->memberOf(\App\Security\RoleConstants::Reviewers)))
<li class="nav-item dropdown">
  <a class="nav-link ed-admin-menu-trigger dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true" aria-label="Admin">
    Admin
  </a>
  <ul class="dropdown-menu dropdown-menu-end ed-admin-menu" aria-label="Admin">
    <li>
      <a class="dropdown-item {{ active('admin.contribution.list') }}" href="{{ route('admin.contribution.list') }}">Contributions</a>
    </li>
    @if (auth()->user()->isAdministrator())
    <li>
      <a class="dropdown-item {{ active('inflection.index') }}" href="{{ route('inflection.index') }}">Inflections</a>
    </li>
    <li>
      <a class="dropdown-item {{ active('speech.index') }}" href="{{ route('speech.index') }}">Type of speech</a>
    </li>
    <li>
      <a class="dropdown-item {{ active('sentence.index') }}" href="{{ route('sentence.index') }}">Phrases</a>
    </li>
    <li>
      <a class="dropdown-item {{ active('gloss.index') }}" href="{{ route('gloss.index') }}">Glossary</a>
    </li>
    <li>
      <a class="dropdown-item {{ active('account.index') }}" href="{{ route('account.index') }}">Accounts</a>
    </li>
    <li>
      <a class="dropdown-item {{ active('word-finder.config.index') }}" href="{{ route('word-finder.config.index') }}">Sage configuration</a>
    </li>
    <li>
      <a class="dropdown-item {{ active('system-error.index') }}" href="{{ route('system-error.index') }}">System errors</a>
    </li>
    @endif
  </ul>
</li>
@endif
