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
          @if ($account->roles->count() < 1)
          <em>This user hasn't been assigned any roles and cannot log in.</em>
          @else
          <ul class="list-group">
          @foreach ($account->roles as $role)
          <li class="list-group-item">
            <a href="{{ route('account.by-role', ['id' => $role->id]) }}">{{ $role->name }}</a>
            <em>{{ \App\Security\RoleConstants::Users === $role->name ? '(Default, user can log in)' : '' }}</em>
            <button type="submit" name="role_id" value="{{ $role->id }}" class="btn btn-secondary btn-sm float-end">
              Remove
            </button>
          </li>
          @endforeach
          </ul>
          @endif
        </form>
        <hr>
        <form method="post" action="{{ route('account.add-membership', ['id' => $account->id]) }}">
          {{ csrf_field() }}
          <div class="input-group">
            <select name="role_id" class="form-control">
              @foreach ($roles as $role)
              <option value="{{ $role->id }}" {{ \App\Security\RoleConstants::Users == $role->name ? 'selected' : '' }}>
                {{ $role->name }}
              </option>
              @endforeach
            </select>
            <span class="input-group-btn">
              <button class="btn btn-secondary" type="submit">Add</button>
            </span>
          </div>
        </form>
        <form method="post" action="{{ route('api.account.delete', ['id' => $account->id]) }}" class="mt-4">
          {{ csrf_field() }}
          {{ method_field('DELETE') }}
          <button class="btn btn-danger" type="submit">Delete account</button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection
