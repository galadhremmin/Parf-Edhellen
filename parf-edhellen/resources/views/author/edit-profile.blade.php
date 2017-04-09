@extends('_layouts.default')

@section('title', $author ? 'Edit ' . $author->Nickname : 'Missing account')

@section('body')
  @if ($author === null)
    This is not the droid you are looking for.
  @else
    
  @endif
@endsection
