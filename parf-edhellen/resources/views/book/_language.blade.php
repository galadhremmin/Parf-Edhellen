<article class="col-sm-{$maxColumnWidth} col-md-{$midColumnWidth} col-lg-{$minColumnWidth}">
    <header>
      <h2 rel="language-box">
      {{ $language->Name }}
      <span class="tengwar">{{ $language->Tengwar }}</span>
      </h2>
    </header>
    <section class="language-box" id="language-box-{{ $language->ID }}">
    @foreach ($glosses as $gloss)
      @include('book._gloss', [ 'gloss' => $gloss ])
    @endforeach
    </section>
  </article>