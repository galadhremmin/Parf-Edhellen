@if (! $review->date_reviewed)
<div class="alert bg-info">
  <strong>Thank you!</strong>
  Your contribution was received @date($review->created_at)
  and is waiting to be reviewed by an administrator.
</div>
@elseif ($review->is_approved)
<div class="alert bg-success">
  <strong>Thank you!</strong>
  Your contribution was approved @date($review->date_reviewed)
  by {{ $review->reviewed_by->nickname }}.
</div>
@else
<div class="alert bg-danger">
  <p>
    <strong>Thank you for your contribution!</strong>
    But unfortunately, your contribution was rejected @date($review->date_reviewed)
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
