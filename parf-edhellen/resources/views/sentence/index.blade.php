@extends('_layouts.default')

@section('title', 'Phrases - Administration')
@section('body')

<h1>Phrases</h1>
{!! Breadcrumbs::render('sentence.index') !!}

<p>Click on a phrase beneath to edit it.</p>

@if (count($sentences) < 1)
<em>No known phrases.</em> 
@else
<ul>
  
</ul>
<a class="btn btn-primary" href="{{ route('sentence.create') }}">Add phrase</a>
@endif

@endsection