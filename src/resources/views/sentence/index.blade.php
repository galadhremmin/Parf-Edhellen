@extends('_layouts.default')

@section('title', 'Phrases - Administration')
@section('body')

<h1>Phrases</h1>
{!! Breadcrumbs::render('sentence.index') !!}

<p>Click on a phrase beneath to edit it.</p>

@if (count($sentences) < 1)
<em>No known phrases.</em> 
@else
  @foreach ($sentences as $languageName => $sentencesForLanguage)
    <h2>{{ $languageName }}</h2>
    <ul>
      @foreach ($sentencesForLanguage as $sentence)
      <li><a href="{{ route('sentence.edit', ['id' => $sentence->id]) }}">{{ $sentence->name }}</a></li>
      @endforeach
    </ul>
  @endforeach
<a class="btn btn-primary" href="{{ route('sentence.create') }}">Add phrase</a>
@endif

@endsection