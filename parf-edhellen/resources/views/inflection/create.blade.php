@extends('_layouts.default')

@section('title', 'Create inflection - Administration')
@section('body')

<h1>Add inflection</h1>
{!! Breadcrumbs::render('inflection.create', $speech) !!}

@include('_shared._errors', [ 'errors' => $errors ])

<form method="post" action="{{ route('inflection.store') }}">
  <div class="form-group">
    <label for="ed-inflection-name" class="control-label">Name</label>
    <input type="text" class="form-control" id="ed-inflection-name" name="name">
  </div>
  <div class="form-group">
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('speech.edit', [ 'id' => $speech->SpeechID ]) }}" class="btn btn-default">Cancel</a>
  </div>
  {{ csrf_field() }}
  <input type="hidden" name="speechId" value="{{ $speech->SpeechID }}">
</form>
@endsection