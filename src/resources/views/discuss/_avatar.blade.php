@inject('link', 'App\Helpers\LinkHelper')
@inject('storage', 'App\Helpers\StorageHelper')

<img src="{{ $storage->accountAvatar($account, true) }}" title="{{ $account ? $account->nickname : 'unknown' }}" class="pp">
