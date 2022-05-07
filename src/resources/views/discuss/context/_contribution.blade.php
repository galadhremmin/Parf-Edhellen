@if ($contribution->is_approved === null)
<span class="badge bg-secondary float-end">Awaiting approval</span>
@elseif ($contribution->is_approved)
<span class="badge bg-success float-end">Approved</span>
@else
<span class="badge bg-danger text-white float-end">Rejected</span>
@endif

<p>These are the comments on <a href="{{ $address }}">{{ $contribution->account->nickname }}'s contribution &ldquo;{{$contribution->word}}&rdquo;</a>.</p>
<div class="mt-3">
@include($viewName, $model)
</div>
<div class="mt-3">
  @include('contribution._status-alert', $model)
</div>