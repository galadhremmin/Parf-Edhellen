@inject('link', 'App\Helpers\LinkHelper')
<ul class="list-group">
  @foreach($auditTrail as $a)
  <li class="list-group-item">
    <span class="date">{{ $a['created_at'] }}</span>
    <a href="{{ $link->author($a['account_id'], $a['account_name']) }}">{{ $a['account_name'] }}</a>
    {!! $a['message'] . ($a['entity'] === null ? '.' : ' '. $a['entity'].'.') !!}
  </li>
  @endforeach
</ul>
