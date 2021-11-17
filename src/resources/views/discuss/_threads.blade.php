@if ($threads->count() < 1) 
<!-- {{ $name }} collection is empty -->
@else
<h2>{{ $name }}</h2>
@foreach ($threads as $thread)
  @include('discuss._thread', [
    'thread' => $thread
  ])
@endforeach
@endif
