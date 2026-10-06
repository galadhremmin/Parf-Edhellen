@extends('_layouts.default')

@section('title', 'Senses awaiting review - Administration')
@section('body')

<h1>Senses awaiting review</h1>
{!! Breadcrumbs::render('sense-review.index') !!}

@if ($waiting < 1)
<p><em>Nothing is waiting. Every sense in the dictionary has been placed.</em></p>
@else
<p>
  {{ number_format($waiting) }} {{ $waiting === 1 ? 'sense' : 'senses' }} could not be placed by rule or by judgement.
  Choose the meaning each one has, and it takes its place in the taxonomy: what it is a kind of, and what kinds
  of it there are.
</p>

<div data-inject-module="sense-review"
     data-inject-prop-waiting="{{ $waiting }}"
     data-inject-prop-by-reason="@json($byReason)"
     data-inject-prop-reasons="@json($reasons)"></div>
@endif

@endsection
