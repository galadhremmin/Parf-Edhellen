@extends('_layouts.default')

@section('title', 'Add type of speech - Administration')
@section('body')

<h1>Add type of speech</h1>
{!! Breadcrumbs::render('speech.create') !!}

@include('_shared._errors', [ 'errors' => $errors ])

<form method="post" action="{{ route('speech.store') }}">
  <div class="form-group">
    <label for="ed-speech-name" class="control-label">Name</label>
    <input type="text" class="form-control" id="ed-speech-name" name="name">
  </div>
  <div class="form-group">
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('speech.index') }}" class="btn btn-default">Cancel</a>
  </div>
  {{ csrf_field() }}
</form>
@endsection
