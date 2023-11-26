@extends('_layouts.default')

@section('title', 'Inflections - Administration')
@section('body')

<h1>Inflections</h1>
{!! Breadcrumbs::render('inflection.index') !!}

<p>Click on an inflection beneath to edit it.</p>

@if (count($inflections) < 1)
  <em>No known type of inflections.</em>
@else
  @foreach ($inflections as $groupName => $inflectionsInGroup)
    <h2>{{ $groupName }}</h2>
    <ul>
    @foreach ($inflectionsInGroup as $inflection)
    <li>
      <a href="{{ route('inflection.edit', ['inflection' => $inflection->id]) }}">
        {{$inflection->name}}
      </a>
    </li>
    @endforeach
    </ul>
  @endforeach
  <a class="btn btn-primary" href="{{ route('inflection.create') }}">Add an inflection</a>
@endif

@endsection
