@extends('_layouts.default')

@section('title', 'Accounts in role '.$role->name.' - Administration')
@section('body')

<h1>Accounts</h1>
{!! Breadcrumbs::render('account.by-role', $role) !!}

<p>
  There are <strong>{{ $accounts->total() }}</strong> accounts in the role <strong>{{ $role->name }}</strong>. They are displayed here 
  in groups of <strong>{{ $accounts->count() }}</strong>, ordered by their nickname.
</p>

@include('account._account-list', [
  'accounts' => $accounts
])
{{ $accounts->links() }}

@endsection
