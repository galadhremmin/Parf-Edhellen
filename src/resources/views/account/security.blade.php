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

<div class="card mb-3 shadow-lg">
  <div class="card-body">
    <h2>Accounts</h2>
    <p>
      Your e-mail address is <strong>{{ $user->email }}</strong>.

      @if ($user->authorization_provider !== null)
        It was provided by {{ $user->authorization_provider->name }}.
      @else
        We don't currently support changing your e-mail address.
      @endif
    </p>

    @if ($user->email_verified_at)
    <p>The following accounts have been registered to your e-mail:</p>

    <form method="post" action="{{ route('account.merge') }}">
      @csrf
      <table class="table table-striped">
        <thead>
          <tr>
            <th></th>
            <th>ID</th>
            <th>Username</th>
            <th>Provider</th>
            <th>Created</th>
            <th>Last modified</th>
            <th>Principal account</th>
          </tr>
        </thead>
        <tbody>
        @foreach ($accounts as $account)
        <tr>
          <td>
            @if (! $account->is_master_account && $account->master_account_id === null && $number_of_accounts > 1)
            <input type="checkbox" name="account_id[]" value="{{ $account->id }}" />
            @endif
          </td>
          <td>{{ $account->id }}</td>
          <td>
            {{ $account->nickname }}
            @if ($account->id === $user->id)
            <em>(current account)</em>
            @endif
          </td>
          <td>
            @if ($account->is_passworded)
            Password
            @else
            {{ $account->authorization_provider()->withTrashed()->first()?->name }}
            @endif
          </td>
          <td>@date($account->created_at)</td>
          <td>@date($account->updated_at)</time></td>
          <td>{{ $account->is_master_account ? 'Yes' : ($account->master_account_id ? 'Linked' : 'Unlinked') }}</td>
        </tr>
        @endforeach
        </tbody>
      </table>
      @if ($number_of_accounts > 1)
      <p>
        You can link your accounts. When you link accounts, your data is moved to a new account (called your principal account) and
        your subsequent logins will be routed to that account. Your accounts will remain, but you will only use them to access your principal account.
      </p>
      <button type="submit" class="btn btn-secondary">Link selected accounts</button>
      @endif
    </form>

    @if ($merge_requests->count() > 0)
    <h3 class="mt-5">Ongoing linking requests</h3>
    <table class="table table-striped">
      <thead>
        <th>Date</th>
        <th>Accounts</th>
        <th>Status</th>
        <th></th>
      </thead>
      <tbody>
        @foreach ($merge_requests as $merge_request)
        <tr>
          <td>@date($merge_request->created_at)</td>
          <td>
            <a href="{{ route('account.merge-status', [ 'requestId' => $merge_request->id ]) }}">{{
              $merge_request_accounts[$merge_request->id]->map(function ($account) {
                return $account->authorization_provider()->withTrashed()->first()?->name.' ('.$account->id.')';
              })->join(', ');
            }}</a>
          </td>
          <td>
            {{ $merge_request->is_fulfilled ? 'Complete' : 'Ongoing' }}
          </td>
          <td>
            @if (! $merge_request->is_fulfilled && ! $merge_request->is_error)
            <form method="post" action="{{ route('account.cancel-merge', [ 'requestId' => $merge_request->id ]) }}">
              @csrf
              <input type="submit" class="btn btn-sm btn-secondary" value="Cancel">
            </form>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif

    @else

    <em>Verify your e-mail address to see your other accounts.</em>

    @endif
  </div>
</div>

@if ($user->email_verified_at)
@ssr('passkey-management', [
    'account' => [
        'id' => $user->id,
        'email' => $user->email,
        'nickname' => $user->nickname,
    ]
], [
    'element' => 'div',
    'attributes' => [
        'class' => 'mt-4',
    ]
])
@endif

<div class="card mb-3 shadow-lg">
  <div class="card-body">
    @if ($user->is_passworded)
    <h2>Change your password</h2>
    <p>
      This account has a password and you can change it as many times as you like. Just remember to pick a secure password, ideally something
      you would store in a password vault.
    </p>
    <form method="post" action="{{ route('account.password') }}">
      @csrf
      <div class="form-group">
        <label for="existing-password" class="form-label">Current password</label>
        <input type="password" name="existing-password" class="form-control" id="existing-password" autocomplete="current-password">
      </div>
      <div class="form-group mt-3">
        <label for="create-password-1" class="form-label">New password</label>
        <input type="password" name="new-password" class="form-control" id="create-password-1" aria-describedby="create-password-1-help" autocomplete="new-password">
        <div id="create-password-1-help" class="form-text">Please pick a password consisting of at least eight characters, numbers and special characters.</div>
      </div>
      <div class="form-group mt-3">
        <label for="create-password-2" class="form-label">Repeat new password</label>
        <input type="password" name="new-password_confirmation" class="form-control" id="create-password-2" autocomplete="new-password">
      </div>
      <div class="text-center mt-3">
        <button type="submit" class="btn btn-secondary">Change password</button>
      </div>
    </form>
    @elseif ($user->is_master_account || $number_of_accounts === 1)
    <h2 class="mt-5">Create a password</h2>
    <p>
      You can only create a password on your principal account. The password can be used to access your account without one an identity provider. This can be useful
      if you've lost or deleted the account you used to have with the identity provider (like Facebook).
    </p>
    <form method="post" action="{{ route('account.password') }}">
      @csrf
      <div class="form-group">
        <label for="create-password-1" class="form-label">Password</label>
        <input type="password" name="new-password" class="form-control" id="create-password-1" aria-describedby="create-password-1-help" autocomplete="new-password">
        <div id="create-password-1-help" class="form-text">Please pick a password consisting of at least eight characters, numbers and special characters.</div>
      </div>
      <div class="form-group mt-3">
        <label for="create-password-2" class="form-label">Repeat password</label>
        <input type="password" name="new-password_confirmation" class="form-control" id="create-password-2" autocomplete="new-password">
      </div>
      <div class="text-center mt-3">
        <button type="submit" class="btn btn-secondary">Create password</button>
      </div>
    </form>
    @endif
  </div>
</div>

<hr class="mt-5 mb-3">
<div class="text-muted">
  <form method="post" action="{{ route('api.account.delete', ['id' => $user->id]) }}" onsubmit="return confirm('Are you sure you want to proceed with irreversible account deletion?');">
    @method('delete')
    @csrf
    <p>
      <strong>Account deletion</strong>.
      Would you like to delete the account {{ $user->nickname }} ({{ $user->id }})? Please <a href="{{ route('about.privacy') }}">read our privacy policy</a>
      before you do. <strong>Account deletion is irreverible!</strong> If you still want to proceed, please click the 
      button below to get started.
      <button type="submit" class="btn btn-link p-0 align-baseline text-danger">Delete account</button>.
    </p>
  </form>
</div>

@endsection
