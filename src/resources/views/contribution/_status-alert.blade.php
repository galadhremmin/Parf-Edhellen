@if (! $contribution->date_reviewed)
<div class="alert bg-info">
  <strong>Thank you!</strong>
  Your contribution was received @date($contribution->created_at)
  and is waiting to be reviewed by an administrator.
</div>
@elseif ($contribution->is_approved)
<div class="alert bg-success">
  <strong>Thank you!</strong>
  Your contribution was approved @date($contribution->date_reviewed)
  by {{ $contribution->reviewed_by->nickname }}.
</div>
@else
<div class="alert bg-warning">
  <p>
    <strong>Thank you for your contribution!</strong>
    But unfortunately, your contribution was rejected @date($contribution->date_reviewed)
    by {{ $contribution->reviewed_by->nickname }}.
    @if (! empty($contribution->justification))
    Reason: @markdown($contribution->justification)
    @endif
  </p>
  <p>
    You are welcome to adapt your submission and submit it for review again.
  </p>
</div>
@endif
