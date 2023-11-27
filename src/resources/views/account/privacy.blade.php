@extends('_layouts.default')

@section('title', 'Privacy')
@section('body')

<h1>Privacy</h1>
{!! Breadcrumbs::render('notifications.index') !!}

@if ($hasMerged)
<dialog open class="alert alert-success">
  <p>
    <strong>Account linking successful!</strong> We've linked the accounts you selected (see table below)
    to your new principal account. We've also initiated the move of the data from your linked accounts to your
    principal accounts. This process will take a little while to complete, so please be patient. Fortunately,
    you can proceed to use your account as you usually would while your data is being moved.
  </p>
  <p class="mb-0">
    We've proceeded to log you in to your principal account. You haven't created a password for this account yet,
    so you will have to use one of your linked accounts (see table below) to log in to this account in the future.
    You don't have to create a password as long as you have access to one of your linked accounts.
  </p>
</dialog>
@endif

<h2>Accounts</h2>
<p>The following accounts have been registered to your e-mail ({{ $user->email }}):</p>

<form method="post" action="{{ route('account.merge') }}">
  @csrf
  <table class="table table-striped">
    <thead>
      <tr>
        <th></th>
        <th>User ID</th>
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
        @if (! $account->is_master_account && $account->master_account_id === null)
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
      <td>{{ $account->authorization_provider?->name }}</td>
      <td>@date($account->created_at)</td>
      <td>@date($account->updated_at)</time></td>
      <td>{{ $account->is_master_account ? 'Yes' : ($account->master_account_id ? 'Linked' : 'Unlinked') }}</td>
    </tr>
    @endforeach
    </tbody>
  </table>
  @if ($errors->any())
  <div class="alert alert-warning">
  @foreach ($errors->all() as $error)
  {{ $error }} 
  @endforeach
  </div>
  @endif
  @if ($accounts->filter(function ($account) { return $account->master_account_id === null && ! $account->is_master_account; })->count() > 0)
  <p>
    You can link your accounts. When you link accounts, your data is moved to a new account (called your principal account) and
    your subsequent logins will be routed to that account. Your accounts will remain, but you will only use them to access your principal account.
  </p>
  <button type="submit" class="btn btn-secondary">Link selected accounts</button>
  @endif
</form>

<!--
<h3 class="mt-5">Create a password</h3>
<p>
  You can only create a password on your principal account. The password can be used to access your account without one an identity provider. This can be useful
  if you've lost or deleted the account you used to have with the identity provider (like Facebook).
</p>
<p>
  <em>Still in development! Coming soon.</em>
</p>
-->

<h3 class="mt-5">Account deletion</h3>
<p>
  Would you like to delete the account {{ $user->nickname }} ({{ $user->id }})? Please <a href="{{ route('about.privacy') }}">read our privacy policy</a>
  before you do. <strong>Account deletion is irreverible!</strong> If you still want to proceed, please click the 
  button below to get started.
</p>

<form method="post" action="{{ route('api.account.delete', ['id' => $user->id]) }}" onsubmit="return confirm('Are you sure you want to proceed with irreversible account deletion?');">
  @method('delete')
  @csrf
  <button type="submit" class="btn btn-secondary">Delete account</button>
</form>

@endsection
