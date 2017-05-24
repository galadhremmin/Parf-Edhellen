@if (count($sections) < 1 )
<div class="row">
  <h3>Forsooth! I can't find what you're looking for!</h3>
  <p>The word <em>{{ $word }}</em> hasn't been recorded for any of the languages.</p>
</div>
@else
<div id="translation-entry" data-module="translation">
  <div>
    <section class="row">
      <?php $c = 0; ?>
      @foreach ($sections as $data)
        @if (! $data['language']->is_unusual)
          @include('book._language', $data)
          <?php $c += 1; ?>
        @endif
      @endforeach
    </section>
    @if (count($sections) > $c) 
    <section class="row">
      <hr />
      <div class="col-xs-12">
          <p>
              <strong>Beware, older languages below!</strong>
              The languages below were invented during Tolkien's earlier period and should be used with caution.
              Remember to never, ever mix words from different languages!
          </p>
      </div>
      @foreach ($sections as $data)
        @if ($data['language']->is_unusual)
          @include('book._language', $data)
        @endif
      @endforeach
    </section>
    @endif
  </div>
</div>
@endif