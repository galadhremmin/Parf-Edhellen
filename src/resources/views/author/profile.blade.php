@extends('_layouts.default')

@section('title', $author ? $author->nickname : 'Missing account')

@section('body')
  @if ($author === null)
    This is not the droid you are looking for.
  @else
  @if (auth()->user()->isAdministrator())
  <div class="bg-dark text-white p-3 mb-3 rounded">
    <a href="{{ route('account.edit', ['account' => $author->id]) }}" class="btn btn-secondary float-end">Edit</a>
    <strong>Administration</strong><br />
    Roles: {{ $author->roles->pluck('name')->implode(', ') }}</br />
    @if ($author->security_events?->count() > 0)
      Last login: @date($author->security_events?->last()?->created_at)<br />
      Last IP: {{ $author->security_events?->last()?->ip_address }}<br />
    @endif
    E-mail verified: {{ $author->email_verified_at ? 'Yes' : 'No' }}<br />
  </div>
  @endif
  @ssr('dashboard-profile', [
    'container'     => 'Profile',
    'account'       => $author,
    'statistics'    => $stats,
    'showJumbotron' => 'true',
    'showDiscuss'   => 'true',
    'showProfile'   => 'true'
  ])
  @endif
@endsection
