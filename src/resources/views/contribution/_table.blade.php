<table class="table table-striped table-hover">
  <thead>
    <tr>
      <th>Submitted date</th>
      <th>Reviewed date</th>
      <th>Word</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($reviews as $review)
    <tr>
      <td>
        @date($review->created_at)
      </td>
      <td>
        @if ($review->date_reviewed)
        @date($review->date_reviewed)
        @else
        -
        @endif
      </td>
      <td>
        <a href="{{ route('contribution.show', ['contribution' => $review->id, 'admin' => isset($admin) && $admin]) }}">{{ $review->word }} ({{ $review->sense }})</a>
      </td>
      <td>
        <a href="{{ $link->author($review->account_id, $review->account->nickname) }}">{{ $review->account->nickname }}</a>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
