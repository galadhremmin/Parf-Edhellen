@if (count($entities['sections']) < 1 )
<h3>Forsooth! I can't find what you're looking for!</h3>
<p>The word <em>{{ $word }}</em> hasn't been recorded for any of the languages.</p>
@else
<?php $leadWithUnusual = $entities['lead_with_unusual'] ?? false; ?>

@if ($leadWithUnusual)
<section class="ed-glossary ed-glossary--unusual {{ $single ? 'ed-glossary--single' : '' }}">
  <p>
      <strong>There are more words but they are from Tolkien's earlier conceptional periods.</strong>
      Tolkien likely changed these words as he evolved the aesthetics and completeness of the languages. You may even find
      languages that Tolkien later rejected. Do not mix words from different time periods unless you are familiar with the
      phonetic developments.
  </p>
  @foreach ($entities['sections'] as $data)
    @if ($data['language']->is_unusual)
      @include('book._language', [
        'language' => $data['language'],
        'lexicalEntries'  => $data['entities'],
        'single'   => $single
      ])
    @endif
  @endforeach
</section>
@endif

<section class="ed-glossary {{ $single ? 'ed-glossary--single' :'' }}">
  @if ($leadWithUnusual)
  <hr />
  <p>
      <strong>Below: Tolkien's Late Period (1950–1973).</strong>
      These are from Tolkien's late conceptual period — his most settled forms of the languages.
      Prefer these over the earlier ones above when composing new text.
  </p>
  @endif
  <?php $c = 0; ?>
  @foreach ($entities['sections'] as $data)
    @if (! $data['language']->is_unusual)
      @include('book._language', [
        'language' => $data['language'],
        'lexicalEntries'  => $data['entities'],
        'single'   => $single
      ])
      <?php $c += 1; ?>
    @endif
  @endforeach
</section>

@if (! $leadWithUnusual && count($entities['sections']) > $c)
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
        'lexicalEntries'  => $data['entities'],
        'single'   => $single
      ])
    @endif
  @endforeach
</section>
@endif
@endif
