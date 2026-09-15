@extends('_layouts.default')

@section('title', 'Permission denied')
@section('body')

<h1>Daro othol!</h1>

<p>
  {{ $exception->getMessage() ?: 'You are not authorized to view this page.' }}
</p>

@endsection
