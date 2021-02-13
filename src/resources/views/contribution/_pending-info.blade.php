
@if (Auth::user()->isAdministrator())
  @if ($review->is_approved === null)
    <hr>
    <form method="post" action="{{ route('contribution.approve', ['id' => $review->id]) }}">
      {{ csrf_field() }}
      {{ method_field('PUT') }}
      <div class="text-right">
        <div class="btn-group" role="group">
          <a href="{{ route('contribution.confirm-reject', ['id' => $review->id]) }}" class="btn btn-warning">
            <span class="glyphicon glyphicon-minus-sign"></span> Reject
          </a>
          <button type="submit" class="btn btn-success">
            <span class="glyphicon glyphicon-ok-sign"></span> Approve
          </button>
        </div>
      </div>
    </form>
  @endif
@endif

@if (! $review->is_approved)
<hr>

You can <a href="{{ route('contribution.edit', ['contribution' => $review->id]) }}">change the submission</a> or 
<a href="{{ route('contribution.confirm-destroy', ['id' => $review->id]) }}">delete the submission</a>. 
If you edit a rejected submission, it will be resubmitted for review; if you edit a pending submission, 
an administrator will review the latest version of your submission.
@endif
