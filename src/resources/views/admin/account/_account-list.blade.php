<table class="table table-striped table-hover">
  <thead>
    <tr>
      <th>ID</th>
      <th>Nickname</th>
      <th>IdP</th>
      <th>E-mail</th>
      <th>Created</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($accounts as $account)
    <tr>
      <td>{{ $account->id }}</td>
      <td>
        <a href="{{ route('account.edit', ['account' => $account->id]) }}">
          @if (preg_match('/^\s+$/', $account->nickname))
          <em>(blank)</em>
          @else
          {{$account->nickname}}
          @endif
        </a>
      </td>
      <td>{{ $account->authorization_provider ? $account->authorization_provider->name : 'n/a' }}</td>
      <td>{{ $account->email }}</td>
      <td>@date($account->created_at)</td>
    </tr>
    @endforeach  
  </tbody>
</table>