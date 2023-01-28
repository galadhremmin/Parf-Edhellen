@if ($threads->count() < 1) 
<!-- {{ $name }} collection is empty -->
@else
<h2>{{ $name }}</h2>
<div class="discuss-table shadow mb-3">
@foreach ($threads as $thread)
  @include('discuss._thread', [
    'thread' => $thread
  ])
@endforeach
</div>
@endif
