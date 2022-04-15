@extends('_layouts.default')

@section('title', 'Add inflection - Administration')
@section('body')

<h1>Add inflection</h1>
{!! Breadcrumbs::render('inflection.create') !!}

@include('_shared._errors', [ 'errors' => $errors ])

<form method="post" action="{{ route('inflection.store') }}">
  <div class="form-group">
    <label for="ed-inflection-name" class="control-label">Name</label>
    <input type="text" class="form-control" id="ed-inflection-name" name="name">
  </div>
  <div class="form-group">
    <label for="ed-inflection-group-name" class="control-label">Group</label>
    <input type="text" class="form-control" id="ed-inflection-group-name" name="group">
  </div>
  <div class="form-group">
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('inflection.index') }}" class="btn btn-secondary">Cancel</a>
  </div>
  {{ csrf_field() }}
</form>
@endsection
