<div class="discuss-table">
  @foreach ($list as $item)
  <div class="r">
    <div class="c">
      @include('discuss._avatar', ['account' => $accounts[$item->id]])
    </div>
    <div class="c p2">
      {{ $accounts[$item->id]->nickname }}
      <p>{{ mb_strimwidth($item->profile, 0, 64, '...') }}</p>
    </div>
    <div class="c text-right">
      {{ $item->$property }}
    </div>
  </div>
  @endforeach
</div>
