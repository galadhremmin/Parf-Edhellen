@extends('_layouts.default')

@section('title', 'Edit '.$inflection->Name.' - Administration')
@section('body')

<h1>{{$inflection->Name}}</h1>
{!! Breadcrumbs::render('inflection.edit', $inflection->speech, $inflection) !!}

<p>
  This inflection is associated with <em>{{ $inflection->speech->Name }}</em>. There are 
  {{ $inflection->sentenceFragments()->count() }} sentence fragments which refer to it.
</p>

@include('_shared._errors', [ 'errors' => $errors ])

<form method="post" action="{{ route('inflection.update', [ 'id' => $inflection->InflectionID ]) }}">
  <div class="form-group">
    <label for="ed-inflection-name" class="control-label">Name</label>
    <input type="text" class="form-control" value="{{ $inflection->Name }}" id="ed-inflection-name" name="name">
  </div>
  <div class="form-group">
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="{{ route('speech.edit', [ 'id' => $inflection->SpeechID ]) }}" class="btn btn-default">Cancel</a>
  </div>
  {{ csrf_field() }}
  {{ method_field('PUT') }}
</form>
<hr>
<form method="post" action="{{ route('inflection.destroy', [ 'id' => $inflection->InflectionID ]) }}">
  <p>Alternatively, you can <button type="submit" class="link-button">delete the inflection</button>.</p>
  {{ csrf_field() }}
  {{ method_field('DELETE') }}
</form>
@endsection