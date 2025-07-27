<article class="ed-glossary__language">
  <header>
    <h2>
    {{ $language->name }}
    <span class="tengwar">{{ $language->tengwar }}</span>
    </h2>
  </header>
  <section class="ed-glossary__language__words" id="language-box-{{ $language->id }}">
    @foreach ($lexicalEntries as $lexicalEntry)
      @include('book._lexical-entry', [ 'lexicalEntry' => $lexicalEntry, 'language' => $language ])

      @if ($single)
        <hr>
        @include('discuss._standalone', [
          'entity_id'   => $lexicalEntry->latest_lexical_entry_version_id,
          'entity_type' => 'lex_entry_ver'
        ])
      @endif
    @endforeach
  </section>
</article>
