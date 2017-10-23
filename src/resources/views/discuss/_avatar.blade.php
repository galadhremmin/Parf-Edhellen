@inject('link', 'App\Helpers\LinkHelper')
@inject('storage', 'App\Helpers\StorageHelper')

<a href="{{ $link->author($account->id, $account->nickname) }}" title="View {{ $account->nickname }}'s profile" class="pp">
  <img src="{{ $storage->accountAvatar($account) }}">
</a>
