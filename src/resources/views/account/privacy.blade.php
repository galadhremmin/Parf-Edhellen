@extends('_layouts.default')

@section('title', 'Privacy')
@section('body')

<h1>Privacy</h1>
{!! Breadcrumbs::render('notifications.index') !!}

<h2>Accounts</h2>
<p>The following accounts have been registered to your e-mail ({{ $user->email }}):</p>

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
  <td><input type="checkbox" name="account_id[]" value="{{ $account->id }}" /></td>
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
  <td>{{ $account->is_master_account ? 'Yes' : 'No' }}</td>
</tr>
@endforeach
</tbody>
</table>

<h3 class="mt-5">Merge accounts</h3>
<p>
  Yes, you can now merge your accounts. When you merge an account, your data is moved to a new account (called your principal account) and
  your subsequent logins will be routed to that account. Your accounts will remain, but you will only use them to access your principal account.
</p>

<form method="post">
  @csrf
  <button type="submit" class="btn btn-secondary">Merge selected accounts</button>
</form>

<h3 class="mt-5">Create a password</h3>
<p>
  You can only create a password on your principal account. The password can be used to access your account without one an identity provider. This can be useful
  if you've lost or deleted the account you used to have with the identity provider (like Facebook).
</p>

<form method="post">
  @csrf
  <button type="submit" class="btn btn-secondary">Save password</button>
</form>

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
