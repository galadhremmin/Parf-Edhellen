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

  @if (@Auth::user()->isAdministrator())
  
  @else
    
  @endif

@endsection
