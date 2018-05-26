@inject('link', 'App\Helpers\LinkHelper')
<div class="r">
  <div class="c">
    @include('discuss._avatar', ['account' => $account])
  </div>
  <div class="c p2 member-list-account">
    <a href="{{ $link->author($account->id, $account->nickname) }}">{{ $account->nickname }}</a>
    @if (! empty($account->tengwar))
    <span class="tengwar">{{ $account->tengwar }}</span>
    @endif
    <p class="profile-summary">
      Joined <span class="date">{{ $account->created_at }}</span>
      &bull; 
      @markdownInline(mb_strimwidth($account->profile, 0, 64, '...'))
    </p>
  </div>
  <div class="c text-right member-list-number">
    @if (isset($detailsView))
      @include($detailsView, ['account' => $account, 'data' => isset($details) ? $details : null])
    @endif
  </div>
</div>