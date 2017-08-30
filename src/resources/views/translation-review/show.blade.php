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
    <strong>Thank you!</strong>
    Your contribution was rejected {{ $review->date_reviewed->format('Y-m-d H:i') }} 
    by {{ $review->reviewed_by->nickname }}.
    @if (! empty($review->justification))
    Reason: {{ $review->justification }}
    @endif
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

  <div class="text-right">
    <div class="btn-group" role="group">
      <a href="{{ route(Auth::user()->isAdministrator() ? 'translation-review.list' : 'translation-review.index') }}" class="btn btn-default">Show all</a> 
      <a href="{{ route('translation-review.edit', ['id' => $review->id]) }}" class="btn btn-default">Change</a> 
      @if ($review->is_approved === null)
      <a href="{{ route('translation-review.confirm-destroy', ['id' => $review->id]) }}" class="btn btn-danger"><span class="glyphicon glyphicon-remove-sign"></span> Delete</a>
      @endif
      @if (Auth::user()->isAdministrator() && $review->is_approved === null)
      <a href="{{ route('translation-review.confirm-reject', ['id' => $review->id]) }}" class="btn btn-warning">
        <span class="glyphicon glyphicon-minus-sign"></span> Reject
      </a>
      <a href="{{ route('translation-review.confirm-approve', ['id' => $review->id]) }}" class="btn btn-success">
        <span class="glyphicon glyphicon-ok-sign"></span> Approve
      </a>
      @endif
    </div>
  </div>

@endsection
