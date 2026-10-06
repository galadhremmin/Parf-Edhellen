@extends('_layouts.default')

@section('title', 'Security')
@section('body')

<h1>Account security</h1>
{!! Breadcrumbs::render('account.security') !!}

@if ($is_merged)
<dialog open class="alert alert-success">
  <p>
    <strong>Account linking successful!</strong> We've linked the accounts you selected (see table below)
    to your new principal account. We've also initiated the move of the data from your linked accounts to your
    principal accounts. This process will take a little while to complete, so please be patient. Fortunately,
    you can proceed to use your account as you usually would while your data is being moved.
  </p>
  <p class="mb-0">
    We've proceeded to log you in to your principal account. You haven't created a password for this account yet,
    so you will have to use one of your linked accounts (see table below) to sign in to this account in the future.
    You don't have to create a password as long as you have access to one of your linked accounts.
  </p>
</dialog>
@endif

@if ($is_passworded)
<dialog open class="alert alert-success">
  <p class="mb-0">
    @if ($is_new_account)
    <strong>We have created a new account with your new password.</strong> Your username will be your e-mail address
    ({{ $user->email }}). Your information will be moved to your new account soon, and your original account has been
    linked to your new account, so you can now decide whether to sign in with your new password <em>or</em> the
    identity provider.
    @else
    <strong>Your password has been changed.</strong> Your username continues to be your e-mail address
    ({{ $user->email }}).
    @endif
  </p>
</dialog>
@endif

@if ($errors->any())
<div class="alert alert-warning">
@foreach ($errors->all() as $error)
{{ $error }} 
@endforeach
</div>
@endif

@if ($user->email_verified_at === null)
<form method="post" action="{{ route('account.resent-verification') }}">
  @csrf
  <dialog open class="alert alert-info">
    @if ($verification_status === 'sent')
    <p>
      <strong>A verification e-mail has been sent to your e-mail address.</strong> Verify your e-mail address by following
      the instructions in the e-mail.
    </p>
    @else
    <p>
      <strong>You haven't verified your e-mail address.</strong> Verify your e-mail address to gain access to all features.
    </p>
    @endif
    <div class="text-center">
      <button class="btn btn-secondary" type="submit">Send verification e-mail</button>
    </div>
  </dialog>
</form>
@elseif ($verification_status === 'ok')
<dialog open class="alert alert-success">Your e-mail address has been successfully verified. Thank you!</dialog>
@endif

@if ($is_released)
<dialog open class="alert alert-success">
  <strong>The other account no longer uses your e-mail address.</strong> You can now link your accounts and create a password.
</dialog>
@endif

@if ($unverified_holder !== null)
<section class="card account-notice mb-4">
  <div class="card-body">
    <p class="ed-label mb-1">Your e-mail address</p>
    <h2 class="card-title">Another account claims {{ $user->email }}</h2>
    <p>
      <strong>{{ $unverified_holder->nickname }}</strong> <span class="ed-ui">#{{ $unverified_holder->id }}</span>
      signed up with a password @date($unverified_holder->created_at) but never confirmed the address.
      Until that's settled you can't link accounts or create a password.
    </p>
    <div class="account-notice__choices">
      <div class="account-notice__choice">
        <h3 class="account-notice__choice-title">It's mine</h3>
        <p>
          <a href="{{ route('logout') }}">Sign out</a>, sign in to it with its password and verify the address there.
          Forgotten the password? <a href="{{ route('auth.forgot-password') }}">Reset it by e-mail</a>.
        </p>
      </div>
      <div class="account-notice__choice">
        <h3 class="account-notice__choice-title">It isn't mine</h3>
        <p>Release it. It keeps what it has posted, but loses the address and can no longer be signed in to.</p>
        <form method="post" action="{{ route('account.release-email', ['accountId' => $unverified_holder->id]) }}"
          onsubmit="return confirm('Release your e-mail address from account {{ $unverified_holder->id }}? That account will no longer be able to sign in.');">
          @csrf
          <button type="submit" class="btn btn-secondary btn-sm">Release it</button>
        </form>
      </div>
    </div>
  </div>
</section>
@endif

<section class="card mb-4">
  <div class="card-body">
    <p class="ed-label mb-1">Signing in</p>
    <h2 class="card-title">Your accounts</h2>
    <p class="ed-panel__lead">
      Registered to <strong>{{ $user->email }}</strong>@if ($user->authorization_provider !== null),
      as given by {{ $user->authorization_provider->name }}@endif.
      <span class="ed-ui">E-mail addresses can't be changed yet.</span>
    </p>

    @if ($user->email_verified_at)
    <form method="post" action="{{ route('account.merge') }}">
      @csrf
      <ul class="ed-list {{ $number_of_accounts > 1 ? 'ed-list--selectable' : '' }}">
        @foreach ($accounts as $account)
        @php($isLinkable = ! $account->is_master_account && $account->master_account_id === null && $number_of_accounts > 1)
        @php($provider = $account->authorization_provider()->withTrashed()->first())
        <li class="ed-list__item">
          @if ($number_of_accounts > 1)
          <span class="ed-list__select">
            @if ($isLinkable)
            <input type="checkbox" class="form-check-input" name="account_id[]" value="{{ $account->id }}"
              id="link-account-{{ $account->id }}" aria-label="Link {{ $account->nickname }}">
            @endif
          </span>
          @endif
          <label class="ed-list__body" @if ($isLinkable) for="link-account-{{ $account->id }}" @endif>
            <span class="ed-list__kind">
              @if ($account->is_passworded)
              Password
              @elseif ($account->is_master_account)
              Principal account
              @else
              {{ $provider?->name }}
              @endif
            </span>
            <span class="ed-list__name">
              <span class="{{ $account->id === $user->id ? 'account-list__current' : '' }}">{{ $account->nickname }}</span>
              <span class="ed-ui">
                #{{ $account->id }}
                @if ($account->id === $user->id)
                · you're signed in with this
                @endif
              </span>
            </span>
            <span class="ed-ui">{{ $account_activity[$account->id] }}</span>
            @if ($provider?->trashed() && $account->master_account_id === null)
            <span class="ed-ui">{{ $provider->name }} sign-in has been retired. Link it to keep what you did with it.</span>
            @elseif ($provider !== null && ! $provider->trashed() && $account->email_verified_at === null && ! $account->is_master_account)
            <span class="ed-ui">You'll confirm it with a code from your inbox the first time you sign in with it.</span>
            @endif
          </label>
          <span class="ed-list__status">
            @if ($account->is_master_account && $account->email_verified_at === null)
            <span class="ed-chip ed-chip--warn">Unverified</span>
            @elseif ($account->is_master_account)
            <span class="ed-chip ed-chip--gild">Principal</span>
            @elseif ($account->master_account_id)
            <span class="ed-chip">Linked</span>
            @elseif ($provider?->trashed())
            <span class="ed-chip ed-chip--muted">Retired</span>
            @else
            <span class="ed-chip ed-chip--muted">Not linked</span>
            @endif
            <span class="ed-ui">created @date($account->created_at)</span>
          </span>
        </li>
        @endforeach
      </ul>

      @if ($number_of_accounts > 1)
      <div class="ed-panel__footer">
        <p class="ed-panel__note">
          <strong>Linking</strong> moves everything to one principal account, which you can then reach with any of them.
          Only link accounts you recognise: check what each one has done.
        </p>
        <button type="submit" class="btn btn-secondary">Link selected accounts</button>
      </div>
      @endif
    </form>

    @if ($merge_requests->count() > 0)
    <p class="ed-label mt-4 mb-2">Linking requests</p>
    <ul class="ed-list">
      @foreach ($merge_requests as $merge_request)
      <li class="ed-list__item">
        <span class="ed-list__body">
          <a class="ed-list__name" href="{{ route('account.merge-status', ['requestId' => $merge_request->id]) }}">{{
            $merge_request_accounts[$merge_request->id]->map(function ($account) {
              return $account->authorization_provider()->withTrashed()->first()?->name.' #'.$account->id;
            })->join(', ')
          }}</a>
          <span class="ed-ui">@date($merge_request->created_at)</span>
        </span>
        <span class="ed-list__status">
          @if ($merge_request->is_fulfilled)
          <span class="ed-chip ed-chip--gild">Complete</span>
          @elseif ($merge_request->is_error)
          <span class="ed-chip ed-chip--warn">Failed</span>
          @else
          <span class="ed-chip">Waiting for your e-mail</span>
          <form method="post" action="{{ route('account.cancel-merge', ['requestId' => $merge_request->id]) }}">
            @csrf
            <button type="submit" class="btn btn-link btn-sm p-0">Cancel</button>
          </form>
          @endif
        </span>
      </li>
      @endforeach
    </ul>
    @endif

    @else
    <p class="ed-panel__empty">Verify your e-mail address (see above) to see every account registered to it.</p>
    @endif
  </div>
</section>

@if ($user->email_verified_at)
@ssr('passkey-management', [
    'account' => [
        'id' => $user->id,
        'email' => $user->email,
        'nickname' => $user->nickname,
    ]
], [
    'element' => 'div',
])
@endif

@php($canCreatePassword = ! $user->is_passworded && ($user->is_master_account || $number_of_accounts === 1))
<section class="card mb-4">
  <div class="card-body">
    <p class="ed-label mb-1">Signing in with a password</p>
    @if ($user->is_passworded)
    <h2 class="card-title">Change your password</h2>
    <p class="ed-panel__lead">You sign in with <strong>{{ $user->email }}</strong> and this password. A password manager is the best place to keep it.</p>
    <form method="post" action="{{ route('account.password') }}" class="account-password">
      @csrf
      <div class="account-password__fields">
        <div class="account-password__field account-password__field--wide">
          <label for="existing-password" class="form-label">Current password</label>
          <input type="password" name="existing-password" class="form-control" id="existing-password" autocomplete="current-password">
        </div>
        <div class="account-password__field">
          <label for="create-password-1" class="form-label">New password</label>
          <input type="password" name="new-password" class="form-control" id="create-password-1" aria-describedby="create-password-help" autocomplete="new-password">
        </div>
        <div class="account-password__field">
          <label for="create-password-2" class="form-label">Repeat new password</label>
          <input type="password" name="new-password_confirmation" class="form-control" id="create-password-2" autocomplete="new-password">
        </div>
      </div>
      <div class="ed-panel__footer">
        <p class="ed-panel__note" id="create-password-help">At least eight characters, with numbers and special characters.</p>
        <button type="submit" class="btn btn-secondary">Change password</button>
      </div>
    </form>
    @elseif ($canCreatePassword && $user->email_verified_at === null)
    <h2 class="card-title">Create a password</h2>
    <p class="ed-panel__empty">Verify your e-mail address (see above) before you create a password.</p>
    @elseif ($canCreatePassword)
    <h2 class="card-title">Create a password</h2>
    <p class="ed-panel__lead">
      A password lets you sign in with <strong>{{ $user->email }}</strong> even if you lose access to
      {{ $user->authorization_provider?->name ?? 'your other sign-in method' }}.
    </p>
    <form method="post" action="{{ route('account.password') }}" class="account-password">
      @csrf
      <div class="account-password__fields">
        <div class="account-password__field">
          <label for="create-password-1" class="form-label">Password</label>
          <input type="password" name="new-password" class="form-control" id="create-password-1" aria-describedby="create-password-help" autocomplete="new-password">
        </div>
        <div class="account-password__field">
          <label for="create-password-2" class="form-label">Repeat password</label>
          <input type="password" name="new-password_confirmation" class="form-control" id="create-password-2" autocomplete="new-password">
        </div>
      </div>
      <div class="ed-panel__footer">
        <p class="ed-panel__note" id="create-password-help">At least eight characters, with numbers and special characters.</p>
        <button type="submit" class="btn btn-secondary">Create password</button>
      </div>
    </form>
    @else
    <h2 class="card-title">Create a password</h2>
    <p class="ed-panel__empty">
      A password belongs to your principal account. Link your accounts (see above) first, then create it from there.
    </p>
    @endif
  </div>
</section>

<form method="post" action="{{ route('api.account.delete', ['id' => $user->id]) }}" class="account-closing"
  onsubmit="return confirm('Delete this account for good? This cannot be undone.');">
  @method('delete')
  @csrf
  <p class="ed-label mb-1">Closing your account</p>
  <p class="mb-0">
    Deleting {{ $user->nickname }} <span class="ed-ui">#{{ $user->id }}</span> can't be undone.
    <a href="{{ route('about.privacy') }}">Our privacy policy</a> explains what happens to your data.
    <button type="submit" class="btn btn-link p-0 align-baseline account-closing__delete">Delete this account</button>
  </p>
</form>

@endsection

@section('styles')
<link rel="stylesheet" href="@assetpath(style-auth.css)">
@endsection
