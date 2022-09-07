@extends('_layouts.default')

@section('title', 'Edit '.$inflection->Name.' - Administration')
@section('body')

<h1>{{$inflection->Name}}</h1>
{!! Breadcrumbs::render('inflection.edit', $inflection) !!}

<p>
  There are {{ $inflection->sentence_fragment_associations()->count() }} sentence fragments which has
  this inflection.
</p>

@include('_shared._errors', [ 'errors' => $errors ])

<form method="post" action="{{ route('inflection.update', [ 'inflection' => $inflection->id ]) }}">
  <div class="form-group">
    <label for="ed-inflection-name" class="control-label">Name</label>
    <input type="text" class="form-control" value="{{ $inflection->name }}" id="ed-inflection-name" name="name">
  </div>
  <div class="form-group">
    <label for="ed-inflection-group-name" class="control-label">Group</label>
    <input type="text" class="form-control" value="{{ $inflection->group_name }}" id="ed-inflection-group-name" name="group">
  </div>
  <div class="form-group">
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('inflection.index') }}" class="btn btn-secondary">Cancel</a>
  </div>
  {{ csrf_field() }}
  {{ method_field('PUT') }}
</form>
@endsection
