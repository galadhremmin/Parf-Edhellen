<table class="table table-striped table-hover">
  <thead>
    <tr>
      <th>Date</th>
      <th>Word</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($reviews as $review)
    <tr>
      <td>
        <time datetime="{{ $review->created_at }}">{{ $review->created_at }}</time>
        @if ($review->date_reviewed)
        &rarr; <time datetime="{{ $review->date_reviewed }}" title="The submission was reviewed {{ $review->date_reviewed }} by {{ $review->reviewed_by->nickname }}.">{{ $review->date_reviewed }}</time>
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
