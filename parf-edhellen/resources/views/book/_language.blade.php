<article class="col-sm-{{ $columnsMax }} col-md-{{ $columnsMid }} col-lg-{{ $columnsMin }}">
    <header>
      <h2 rel="language-box">
      {{ $language->Name }}
      <span class="tengwar">{{ $language->Tengwar }}</span>
      </h2>
    </header>
    <section class="language-box" id="language-box-{{ $language->ID }}">
    @foreach ($glosses as $gloss)
      @include('book._gloss', [ 'gloss' => $gloss, 'language' => $language ])
    @endforeach
    </section>
  </article>
