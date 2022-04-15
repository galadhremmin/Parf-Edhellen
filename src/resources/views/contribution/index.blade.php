@extends('_layouts.default')

@section('title', 'Contributions')
@section('body')
<h1>Contribute</h1>

{!! Breadcrumbs::render('contribution.index') !!}

<div class="card shadow-lg mb-3">
  <div class="card-body">

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
