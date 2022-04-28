<article class="ed-glossary__language">
  <header>
    <h2>
    {{ $language->name }}
    <span class="tengwar">{{ $language->tengwar }}</span>
    </h2>
  </header>
  <section class="ed-glossary__language__words" id="language-box-{{ $language->id }}">
    @foreach ($glosses as $gloss)
      @include('book._gloss', [ 'gloss' => $gloss, 'language' => $language ])

      @if ($single)
        <hr>
        @include('discuss._standalone', [
          'entity_id'   => $gloss->latest_gloss_version_id,
          'entity_type' => 'glossv'
        ])
      @endif
    @endforeach
  </section>
</article>
