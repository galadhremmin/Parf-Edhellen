@inject('link', 'App\Helpers\LinkHelper')
<ul class="ed-home-activity-list">
  @foreach($auditTrail as $a)
  <li>
    <a href="{{ $link->author($a['account_id'], $a['account_name']) }}"
       title="View {{ $a['account_name'] }}'s profile">
      <span class="ed-home-activity-avatar" style="background-image:url({{ $a['account_avatar'] }})"></span>
      {{ $a['account_name'] }}
    </a>
    {!! $a['message'] . ($a['entity'] === null ? '.' : ' '. $a['entity'].'.') !!}
    @date($a['created_at'])
  </li>
  @endforeach
</ul>
