@extends('_layouts.default')

@section('title', 'Privacy')
@section('body')

<h1>Privacy</h1>
{!! Breadcrumbs::render('mail-setting.index') !!}

<h2>Accounts</h2>
<p>The following accounts have been registered to your e-mail ({{ $user->email }}):</p>

<table class="table table-striped">
<thead>
  <tr>
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

<h2>Account deletion</h2>
<p>
  Would you like to delete your account? Please <a href="{{ route('about.privacy') }}">read our privacy policy</a>
  before you do. <strong>Account deletion is irreverible!</strong> If you still want to proceed, please click the 
  button below to get started.
</p>

<form method="post" action="{{ route('api.account.delete', ['id' => $user->id]) }}" onsubmit="return confirm('Are you sure you want to proceed with irreversible account deletion?');">
  @method('delete')
  @csrf
  <div class="text-end">
    <button type="submit" class="btn btn-secondary">Delete account</button>
  </div>
</form>

@endsection
