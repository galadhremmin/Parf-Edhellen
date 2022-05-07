
@if (Auth::user()->isAdministrator())
  @if ($contribution->is_approved === null)
    <hr>
    <form method="post" action="{{ route('contribution.approve', ['id' => $contribution->id]) }}">
      {{ csrf_field() }}
      {{ method_field('PUT') }}
      <div class="text-end">
        <div class="btn-group" role="group">
          <a href="{{ route('contribution.confirm-reject', ['id' => $contribution->id]) }}" class="btn btn-warning">
            <span class="TextIcon TextIcon--trash"></span> Reject
          </a>
          <button type="submit" class="btn btn-success">
            <span class="TextIcon TextIcon--ok bg-black"></span> Approve
          </button>
        </div>
      </div>
    </form>
  @endif
@endif

@if (! $contribution->is_approved)
<hr>

You can <a href="{{ route('contribution.edit', ['contribution' => $contribution->id]) }}">change the submission</a> or 
<a href="{{ route('contribution.confirm-destroy', ['id' => $contribution->id]) }}">delete the submission</a>. 
If you edit a rejected submission, it will be resubmitted for review; if you edit a pending submission, 
an administrator will review the latest version of your submission.
@endif
