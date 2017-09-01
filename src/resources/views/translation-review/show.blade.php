@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Contribute')
@section('body')
  <h1>Contribution #{{ $review->id }}</h1>
  
  {!! Breadcrumbs::render('translation-review.show', $review->id) !!}

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

  <div class="well">
    @foreach ($sections as $section)
      @foreach ($section['glosses'] as $gloss)
        @include('book._gloss', [ 
          'gloss' => $gloss, 
          'language' => $section['language'],
          'disable_tools' => true
        ])
      @endforeach
    @endforeach

    @foreach ($keywords as $keyword) 
      <span class="label label-default">{{ $keyword }}</span>
    @endforeach
  </div>

  @if (! empty($review->notes))
  <div class="well">
    <strong>Author's notes</strong>
    <p>{{ $review->notes }}</p>
  </div>
  @endif

  @if (! $review->is_approved)
  <form method="post" action="{{ route('translation-review.approve', ['id' => $review->id]) }}">
    {{ csrf_field() }}
    {{ method_field('PUT') }}
    <div class="text-right">
      <div class="btn-group" role="group">
        @if ($review->is_approved === null)
        <a href="{{ route('translation-review.confirm-destroy', ['id' => $review->id]) }}" class="btn btn-danger"><span class="glyphicon glyphicon-remove-sign"></span> Delete</a>
        @endif
        @if (Auth::user()->isAdministrator() && $review->is_approved === null)
        <a href="{{ route('translation-review.confirm-reject', ['id' => $review->id]) }}" class="btn btn-warning">
          <span class="glyphicon glyphicon-minus-sign"></span> Reject
        </a>
        <button type="submit" class="btn btn-success">
          <span class="glyphicon glyphicon-ok-sign"></span> Approve
        </button>
        @endif
      </div>
    </div>
  </form>

  <hr>

  You can also <a href="{{ route('translation-review.edit', ['id' => $review->id]) }}">change the submission</a>. If your submission was
  rejected, it will be resubmitted for review. If it hasn't been reviewed by an administrator yet, the administrator will review the latest version of your submission.
  @else
  <hr>
  You can also <a href="{{ $link->translation($review->translation_id) }}">visit the gloss in the dictionary</a>.
  @endif

@endsection
