@extends('_layouts.default')

@section('title', 'Accounts - Administration')
@section('body')

<h1>Accounts</h1>
{!! Breadcrumbs::render('account.index') !!}

<p>
  There are <strong>{{ $accounts->total() }}</strong> accounts in the database. They are displayed here 
  in groups of <strong>{{ $accounts->count() }}</strong>, ordered by their nickname.
</p>

<div class="card shadow-lg mb-3">
  <div class="card-body">
    <form method="get">
      <div class="input-group mb-3">
        <input type="text" class="form-control" id="filter-params" placeholder="Custom filter..." name="filter" value="{{ isset($filter) ? $filter : '' }}">
        <button type="submit" class="btn btn-secondary">Apply</button>
      </div>
    </form>
    @include('admin.account._account-list', [
      'accounts' => $accounts
    ])
    {{ $accounts->links() }}
  </div>
</div>


<div class="card shadow-lg mb-3">
  <div class="card-body">
    <h2>Recently deleted accounts</h2>
    <ol>
    @foreach ($deletedAccounts as $account)
    <li><a href="{{ route('account.edit', ['account' => $account]) }}">{{ $account->id }}</a> deleted @date($account->updated_at) (created @date($account->created_at))</li>
    @endforeach
    </ol>
  </div>
</div>

@endsection
