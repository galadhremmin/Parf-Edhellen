@if (! $review->date_reviewed)
<div class="alert alert-info">
  <strong>Thank you!</strong>
  Your contribution was received {{ $review->created_at->format('Y-m-d H:i') }} 
  and is waiting to be reviewed by an administrator.
</div>
@elseif ($review->is_approved)
<div class="alert alert-success">
  <strong>Thank you!</strong>
  Your contribution was approved {{ $review->date_reviewed->format('Y-m-d H:i') }} 
  by {{ $review->reviewed_by->nickname }}.
</div>
@else
<div class="alert alert-danger">
  <p>
    <strong>Thank you for your contribution!</strong>
    But unfortunately, your contribution was rejected {{ $review->date_reviewed->format('Y-m-d H:i') }} 
    by {{ $review->reviewed_by->nickname }}.
    @if (! empty($review->justification))
    Reason: {{ $review->justification }}
    @endif
  </p>
  <p>
    You are welcome to adapt your submission and submit it for review again.  You can do that by accessing
    the form beneath.
  </p>
</div>
@endif
