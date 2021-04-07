@if (count($entities['sections']) < 1 )
<h3>Forsooth! I can't find what you're looking for!</h3>
<p>The word <em>{{ $word }}</em> hasn't been recorded for any of the languages.</p>
@else
<section class="ed-glossary {{ $single ? 'ed-glossary--single' :'' }}">
  <?php $c = 0; ?>
  @foreach ($entities['sections'] as $data)
    @if (! $data['language']->is_unusual)
      @include('book._language', [
        'language' => $data['language'],
        'glosses'  => $data['entities'],
        'single'   => $single
      ])
      <?php $c += 1; ?>
    @endif
  @endforeach
</section>
@if (count($entities['sections']) > $c) 
<section class="ed-glossary ed-glossary--unusual {{ $single ? 'ed-glossary--single' : '' }}">
  <hr />
  <p>
      <strong>Beware, older languages below!</strong>
      The languages below were invented during Tolkien's earlier period and should be used with caution.
      Remember to never, ever mix words from different languages!
  </p>
  @foreach ($entities['sections'] as $data)
    @if ($data['language']->is_unusual)
      @include('book._language', [
        'language' => $data['language'],
        'glosses'  => $data['entities'],
        'single'   => $single
      ])
    @endif
  @endforeach
</section>
@endif
@endif
