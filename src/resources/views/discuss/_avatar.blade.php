@inject('link', 'App\Helpers\LinkHelper')
@inject('storage', 'App\Helpers\StorageHelper')

<a href="{{ $account ? $link->author($account->id, $account->nickname) : '' }}" title="View {{ $account ? $account->nickname : 'unknown' }}'s profile" class="pp">
  <img src="{{ $storage->accountAvatar($account, true) }}">
</a>
