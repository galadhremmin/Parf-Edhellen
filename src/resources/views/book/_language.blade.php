<article class="col-sm-{{ $columnsMax }} col-md-{{ $columnsMid }} col-lg-{{ $columnsMin }}">
  <header>
    <h2 rel="language-box">
    {{ $language->name }}
    <span class="tengwar">{{ $language->tengwar }}</span>
    </h2>
  </header>
  <section class="language-box" id="language-box-{{ $language->id }}">
    @foreach ($glosses as $gloss)
      @include('book._gloss', [ 'gloss' => $gloss, 'language' => $language ])

      @if ($single)
        <hr>
        @include('_shared._comments', [
          'morph'      => 'translation',
          'entity_id'  => $gloss->id,
          'enabled'    => true
        ])
      @endif
    @endforeach
  </section>
</article>
