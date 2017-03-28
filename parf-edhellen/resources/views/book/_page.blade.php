@if (count($sections) < 1 )
<div class="row">
  <h3>Forsooth! I can't find what you're looking for!</h3>
  <p>The word <em>{{ $word }}</em> hasn't been recorded for any of the languages.</p>
</div>
@else
<div id="translation-entry" data-module="translation">
  <div class="row">
    @foreach ($sections as $language)
      @include('book._language', $language)
    @endforeach
  </div>
</div>
@endif