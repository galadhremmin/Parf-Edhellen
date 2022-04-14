@if (! $review->date_reviewed)
<div class="alert alert-info">
  <strong>Thank you!</strong>
  Your contribution was received  <time datetime="{{ $review->created_at }}">{{ $review->created_at }}</time>
  and is waiting to be reviewed by an administrator.
</div>
@elseif ($review->is_approved)
<div class="alert alert-success">
  <strong>Thank you!</strong>
  Your contribution was approved <time datetime="{{ $review->date_reviewed }}">{{ $review->date_reviewed }}</time>
  by {{ $review->reviewed_by->nickname }}.
</div>
@else
<div class="alert alert-danger">
  <p>
    <strong>Thank you for your contribution!</strong>
    But unfortunately, your contribution was rejected <time class="{{ $review->date_reviewed }}">{{ $review->date_reviewed }}</time>
    by {{ $review->reviewed_by->nickname }}.
    @if (! empty($review->justification))
    Reason: @markdown($review->justification)
    @endif
  </p>
  <p>
    You are welcome to adapt your submission and submit it for review again.  You can do that by accessing
    the form beneath.
  </p>
</div>
@endif
