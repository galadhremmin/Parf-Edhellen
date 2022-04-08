@extends('_layouts.default')

@section('title', 'Account '.$account->nickname.' - Administration')
@section('body')

<h1>Account {{ $account->nickname }}</h1>
{!! Breadcrumbs::render('account.edit', $account) !!}

<div class="row">
  <div class="col-sm-6">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h2 class="panel-title">Audit trail</h2>
      </div>
      <div class="panel-body">
        @include('_shared._audit-trail', [
        'auditTrail' => $auditTrail
        ])
        {{ $auditTrailPagination->links() }}
      </div>
    </div>
  </div>
  <div class="col-sm-6">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h2 class="panel-title">Roles</h2>
      </div>
      <div class="panel-body">
        <form method="post" action="{{ route('account.delete-membership', ['id' => $account->id]) }}">
          {{ csrf_field() }}
          {{ method_field('DELETE') }}
          <ul class="list-group">
          @foreach ($account->roles as $role)
          <li class="list-group-item">
            <a href="{{ route('account.by-role', ['id' => $role->id]) }}">{{ $role->name }}</a>
            <button type="submit" name="role_id" value="{{ $role->id }}" class="btn btn-secondary btn-sm pull-right">
              Remove
            </button>
          </li>
          @endforeach
          </ul>
        </form>
        <hr>
        <form method="post" action="{{ route('account.add-membership', ['id' => $account->id]) }}">
          {{ csrf_field() }}
          <div class="input-group">
            <select name="role_id" class="form-control">
              @foreach ($roles as $role)
              <option value="{{ $role->id }}">{{ $role->name }}</option>
              @endforeach
            </select>
            <span class="input-group-btn">
              <button class="btn btn-secondary" type="submit">Add</button>
            </span>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection
