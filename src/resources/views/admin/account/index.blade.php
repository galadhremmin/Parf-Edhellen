@extends('_layouts.default')

@section('title', 'Accounts - Administration')
@section('body')

<h1>Accounts</h1>
{!! Breadcrumbs::render('account.index') !!}

<p>
  There are <strong>{{ $accounts->total() }}</strong> accounts in the database. They are displayed here 
  in groups of <strong>{{ $accounts->count() }}</strong>, ordered by their nickname.
</p>

<div class="panel panel-default">
  <div class="panel-heading">
    <h2 class="panel-title">Filter</h2>
  </div>
  <div class="panel-body">
    <form class="form-inline" method="get">
      <div class="form-group">
        <label for="filter-params">Filter</label>
        <input type="text" class="form-control" id="filter-params" name="filter" value="{{ isset($filter) ? $filter : '' }}">
      </div>
      <button type="submit" class="btn btn-secondary">Apply</button>
    </form>
  </div>
</div>

@include('admin.account._account-list', [
  'accounts' => $accounts
])
{{ $accounts->links() }}

<h2>Recently deleted accounts</h2>
<ol>
@foreach ($deletedAccounts as $account)
<li><a href="{{ route('account.edit', ['account' => $account]) }}">{{ $account->id }}</a> deleted @date($account->updated_at) (created @date($account->created_at))</li>
@endforeach
</ol>

@endsection
