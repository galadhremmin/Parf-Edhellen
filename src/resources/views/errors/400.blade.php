@extends('_layouts.default')

@section('title', 'Server error')
@section('body')

<h1>Lá hanyan quettalyar!</h1>

<p>
  The server did not understand your request. This usually happens when you pass erroneous data 
  to a method.
</p>
<p>
    You can try to refresh the page, and if that doesn't work, try again later.
</p>
<p>
    Sorry for the inconvenience!
</p>

@include('errors._stack')

@endsection
