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
        {{ $review->created_at->format('Y-m-d H:i') }}
        @if ($review->date_reviewed)
        &rarr; <span title="The submission was reviewed {{ $review->date_reviewed }} by {{ $review->reviewed_by->nickname }}.">{{ $review->date_reviewed->format('Y-m-d H:i') }}</span>
        @endif
      </td>
      <td>
        <a href="{{ route('translation-review.show', ['id' => $review->id]) }}">{{ $review->word }} ({{ $review->sense }})</a>
      </td>
      <td>
        <a href="{{ $link->author($review->account_id, $review->account->nickname) }}">{{ $review->account->nickname }}</a>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
