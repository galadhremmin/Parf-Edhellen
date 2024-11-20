@extends('_layouts.default')
@inject('link', 'App\Helpers\LinkHelper')

@section('title', 'Account '.$account->nickname.' - Administration')
@section('body')

<h1>Account {{ $account->nickname }}</h1>
{!! Breadcrumbs::render('account.edit', $account) !!}

<div class="row">
  <div class="col-sm-6">
    <div class="card shadow-lg">
      <div class="card-body">
        <h2>Audit trail</h2>
        @include('_shared._audit-trail', [
        'auditTrail' => $auditTrail
        ])
        {{ $auditTrailPagination->links() }}
      </div>
    </div>
  </div>
  <div class="col-sm-6">
    <div class="card shadow-lg mb-3">
      <div class="card-body">
        <h2>Metadata</h2>
        <table class="table">
          <tbody>
            <tr>
              <th>Authorization provider</th>
              <td>{{ $account->authorization_provider?->name ?: ($account->is_passworded ? 'Passworded' : 'Unknown') }}</td>
            </tr>
            <tr>
              <th>Deleted?</th>
              <td>{{ $account->is_deleted ? 'Yes' : 'No' }}</td>
            </tr>
            <tr>
              <th>Passworded?</th>
              <td>{{ $account->is_passworded ? 'Yes' : 'No' }}</td>
            </tr>
            <tr>
              <th>Avatar?</th>
              <td>
                {{ $account->has_avatar ? 'Yes' : 'No' }}
              </td>
            </tr>
            <tr>
              <th>Master account?</th>
              <td>
                {{ $account->is_master_account ? 'Yes' : 'No' }}
                @if (! $account->is_master_account && $account->master_account_id)
                (<a href="{{ route('account.edit', ['account' => $account->master_account]) }}">{{ $account->master_account->nickname }}</a>)
                @endif
              </td>
            </tr>
            <tr>
              <th>Verified?</th>
              <td>
                @if ($account->email_verified_at === null)
                  No
                @else
                  @date($account->email_verified_at)
                @endif
                (<a href="mailto:{{ $account->email }}">{{ $account->email }}</a>)
                @if ($account->email_verified_at === null)
                  <form method="post" action="{{ route('api.account.verify-email', ['id' => $account->id]) }}" class="float-end"
                    onsubmit="return confirm('Are you sure you want to forcefully verify the account\'s e-mail address? This is usually an operation that should be performed by the account owner.')">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    <input type="hidden" name="is_verified" value="1">
                    <button class="btn btn-sm btn-secondary" type="submit">Verify</button>
                  </form>
                @else
                  <form method="post" action="{{ route('api.account.verify-email', ['id' => $account->id]) }}" class="float-end"
                    onsubmit="return confirm('Are you sure you want to forcefully unverify the account\'s e-mail address?')">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                    <input type="hidden" name="is_verified" value="0">
                    <button class="btn btn-sm btn-secondary" type="submit">Unverify</button>
                  </form>
                @endif
              </td>
            </tr>
            <tr>
              <th>Created</th>
              <td>@date($account->created_at)</td>
            </tr>
            <tr>
              <th>Updated</th>
              <td>@date($account->updated_at)</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    @if ($account->is_master_account)
    <div class="card shadow-lg mb-3">
      <div class="card-body">
        <h2>Linked accounts</h2>
        <table class="table">
          <tbody>
          @foreach ($account->linked_accounts as $linked)
          <tr>
            <th><a href="{{ route('account.edit', ['account' => $linked]) }}">{{ $linked->nickname }}</a></th>
            <td>{{ $linked->id }}</td>
            <td>{{ $linked->authorization_provider?->name ?: 'Unknown' }}</td>
            <td>@date($linked->created_at)</td>
          </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endif
    <div class="card shadow-lg mb-3">
      <div class="card-body">
        <h2>Roles</h2>
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
        <form method="post" action="{{ route('account.add-membership', ['id' => $account->id]) }}">
          {{ csrf_field() }}
          <div class="input-group mt-3">
            <select name="role_id" class="form-control">
              @foreach ($roles as $role)
              @if (! $account->roles->contains(function($v) use($role) { return $v->id === $role->id; }))
              <option value="{{ $role->id }}"
                {{ \App\Security\RoleConstants::Users == $role->name ? 'selected' : '' }}>
                {{ $role->name }}
              </option>
              @endif
              @endforeach
            </select>
            <button class="btn btn-secondary" type="submit">Add</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<hr>

<form method="post" action="{{ route('api.account.delete', ['id' => $account->id]) }}" onsubmit="return confirm('Are you sure you want to delete this account? This is an irreversible operation.')">
  {{ csrf_field() }}
  {{ method_field('DELETE') }}
  <a href="{{ $link->author($account->id, $account->nickname) }}" class="btn btn-secondary">
    View profile
  </a>
  <button class="btn btn-danger" type="submit">Delete account</button>
</form>
@endsection
