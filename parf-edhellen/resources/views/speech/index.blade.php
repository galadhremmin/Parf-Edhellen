@extends('_layouts.default')

@section('title', 'Type of speeches - Administration')
@section('body')

<h1>Speech</h1>
{!! Breadcrumbs::render('speech.index') !!}

<p>Click on a type of speech beneath to edit it.</p>

@if (count($speeches) < 1)
<em>No known type of speeches.</em> 
@else
<ul>
  @foreach ($speeches as $s)
  <li><a href="{{ route('speech.edit', ['speech' => $s->SpeechID]) }}">{{$s->Name}}</a></li>
  @endforeach
</ul>
<a class="btn btn-primary" href="{{ route('speech.create') }}">Add type of speech</a>
@endif

@endsection