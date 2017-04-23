@extends('_layouts.default')

@section('title', 'Edit '.$speech->name.' - Administration')
@section('body')

<h1>{{$speech->Name}}</h1>
{!! Breadcrumbs::render('speech.edit', $speech) !!}
<p>
  This type of speech has the ID {{$speech->id}}. There are {{$speech->sentenceFragments()->count()}} 
  sentence fragments which refer to it.
</p>

@include('_shared._errors', [ 'errors' => $errors ])

<form method="post" action="{{ route('speech.update', [ 'speech' => $speech->id ]) }}">
  <div class="form-group">
    <label for="ed-speech-name" class="control-label">Name</label>
    <input type="text" class="form-control" value="{{ $speech->name }}" id="ed-speech-name" name="name">
  </div>
  <div class="form-group">
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('speech.index') }}" class="btn btn-default">Cancel</a>
  </div>
  {{ csrf_field() }}
  {{ method_field('PUT') }}
</form>

<hr>
<form method="post" action="{{ route('speech.destroy', [ 'speech' => $speech->id ]) }}">
  <p>Alternatively, you can <button type="submit" class="link-button">delete the type of speech</button>.</p>
  {{ csrf_field() }}
  {{ method_field('DELETE') }}
</form>
@endsection