@extends('_layouts.default')

@section('title', 'Contribute')
@section('body')
  <h1>Contribution #{{ $review->id }}</h1>
  
  {!! Breadcrumbs::render('translation-review.show', $review->id) !!}

  @if (! $review->date_reviewed)
  <div class="alert alert-info">
    <strong>Thank you!</strong>
    Your contribution is awaiting approval. It was received {{ $review->created_at->format('Y-m-d H:i') }} 
    by our admin team.
  </div>
  @elseif ($review->is_approved)
  <div class="alert alert-success">
    <strong>Thank you!</strong>
    Your contribution was approved {{ $review->date_reviewed->format('Y-m-d H:i') }} 
    by {{ $review->reviewed_by->nickname }}.
  </div>
  @else
  <div class="alert alert-success">
    <strong>Thank you!</strong>
    Your contribution was approved {{ $review->date_reviewed->format('Y-m-d H:i') }} 
    by {{ $review->reviewed_by->nickname }}.
  </div>
  @endif

  <div class="well">
    @include('book._gloss', [ 
      'gloss' => $translation, 
      'language' => $translation->language,
      'disable_tools' => true
    ])

    @foreach ($keywords as $keyword) 
      <span class="label label-default">{{ $keyword }}</span>
    @endforeach
  </div>

  @if (@Auth::user()->isAdministrator())
  
  @else
    
  @endif

@endsection
