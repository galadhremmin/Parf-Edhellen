@extends('_layouts.default')

@section('title', $word)

@section('body')
@include('book._page', $sections)
@endsection
