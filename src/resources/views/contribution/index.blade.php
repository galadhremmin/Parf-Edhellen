@extends('_layouts.default')

@section('title', 'Contributions')
@section('body')
<h1>Contribute</h1>

{!! Breadcrumbs::render('contribution.index') !!}

<div class="card shadow-lg mb-3">
  <div class="card-body">
    <h2>New contribution</h2>
    <p>
      Everyone can contribute, but to ensure that we maintain a high bar on quality and protect ourselves against abusive content, each contribution must be reviewed and approved.
      The process for getting your contribution approved has the following steps:
    </p>
    <ol>
      <li>You submit as much information about your gloss or phrase as possible via the forms below.</li>
      <li>An administrator reviews the contribution and requests additional information, if necessary. This usually happens because you have provided insufficient source information.</li>
      <li>Once approved, the contribution is created in your name and highlighted on the front page! You get full attribution for your work.</li>
    </ol>
    <p>
      To get started, click on the button that corresponds to what you would like to add below.
    </p>
    <div class="text-center">
      <a href="{{ route('contribution.create', ['morph' => 'gloss']) }}" class="btn btn-primary">Add gloss</a>
      <a href="{{ route('contribution.create', ['morph' => 'sentence']) }}" class="btn btn-primary">Add phrase</a>
    </div>
  </div>
</div>
<div class="card mb-3">
  <div class="card-body">
    <h2>Pending approval</h2>
    @include('contribution._contributions-table', [
      'reviews' => $pendingReviews
    ])
  </div>
</div>
<div class="card mb-3">
  <div class="card-body">
    <h2>Approved</h2>
    @include('contribution._contributions-table', [
      'reviews' => $approvedReviews
    ])
  </div>
</div>
<div class="card mb-3">
  <div class="card-body">
    <h2>Rejected</h2>
    @include('contribution._contributions-table', [
      'reviews' => $rejectedReviews
    ])
  </div>
</div>
@endsection
