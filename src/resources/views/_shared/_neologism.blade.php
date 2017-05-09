@inject('link', 'App\Helpers\LinkHelper')

<div class="alert alert-warning">
  <p>
    <b><span class="glyphicon glyphicon-warning-sign"></span> Neologism!</b> 
    This text is a neologism, which means that it was not composed by Tolkien himself.
    The content presented herein should therefore always be considered as experimental
    and subject of debate. 
  </p>
  @if ($account)
  <p>
    Consider
    <a href="{{ $link->author($account->id, $account->nickname) }}">
      {{ $account->nickname }}</a> the author of this work.
  </p>
  @endif
</div>