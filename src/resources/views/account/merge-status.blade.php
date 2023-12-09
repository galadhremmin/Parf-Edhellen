@extends('_layouts.default')

@section('title', 'Security')
@section('body')

<h1>Account linking status</h1>
{!! Breadcrumbs::render('account.merge-status', $mergeRequest) !!}

<p>This is a request to link the accounts in the table below. It was initiated on @date($mergeRequest->created_at) and last modified @date($mergeRequest->updated_at).</p>

<table class="table table-striped">
  <thead>
    <tr>
      <th>ID</th>
      <th>Username</th>
      <th>Provider</th>
      <th>Created</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($accounts as $account)
    <tr>
      <td>{{ $account->id }}</td>
      <td>{{ $account->nickname }}</td>
      <td>{{ $account->authorization_provider()->withTrashed()->first()?->name ?: 'Password' }}</td>
      <td>@date($account->created_at)</td>
    </tr>
    @endforeach
  </tbody>
</table>

@if ($mergeRequest->is_error)
<dialog class="alert alert-danger" open>
<p>The request failed with an error:</p>

<pre>{{ $mergeRequest->error }}</pre>
</dialog>
@elseif ($mergeRequest->is_fulfilled)
<dialog class="alert alert-success" open>
  <p>This request has been <strong>fulfilled</strong>. Your accounts have been successfully linked and we have automatically logged you in to your principal account.</p>
</dialog>
@else
<dialog class="alert alert-info" open>
<p>This request is pending confirmation from <strong>{{ $mergeRequest->account?->email }}</strong>. Please check your e-mail inbox.</p>
</dialog>
@endif

@endsection
