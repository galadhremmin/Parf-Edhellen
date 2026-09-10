@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Welcome!')
@section('description', __('home.description', ['words' => number_format($noOfWords), 'sentences' => number_format($noOfSentences)]))
@section('body-class', 'home-page')

{{-- Everything above the search bar is welcome, and it is disposable: the
     moment results appear, index.scss collapses this and .home-welcome away.
     That is what lets the page carry an argument without costing a regular
     visitor anything -- they type immediately and never see it. --}}
@section('before-search')
<header class="home-hero">
  <p class="ed-label home-hero__greeting">Mae govannen &nbsp;&#10022;&nbsp; well met</p>
  <h1 class="home-hero__title">Every elvish word, traced back to where Tolkien wrote it</h1>
  <div class="ed-rule home-hero__rule">&#10022;</div>
  <p class="home-hero__subtitle">
    @number($noOfWords) words of Sindarin, Quenya, Telerin and the tongues around them —
    each one carrying the book, the page and the hand it came from.
  </p>
</header>
@endsection

@section('body')

<div class="home-welcome">

  <p class="ed-label home-assurances">
    Free &nbsp;&#10022;&nbsp; no account needed &nbsp;&#10022;&nbsp; open source since 2011
  </p>

  {{-- Why this book, and not the first search result --}}
  <section class="home-section home-section--argument">
    <header class="home-section__header">
      <p class="ed-label">The first chapter</p>
      <h2 class="home-section__title">A book of sources, not a list of words</h2>
      <p class="home-section__lead">
        Tolkien revised his languages for fifty years and finished almost nothing. So this book
        never hands you a bare answer — it shows you who wrote the word down, where, and how
        sure they were.
      </p>
    </header>

    <div class="home-proofs">
      <article class="home-proof">
        <span class="home-proof__mark" aria-hidden="true">&#10022;</span>
        <h3 class="home-proof__title">Every word carries its provenance</h3>
        <p>Source, publication, page, editor and the full history of revisions — down to the
          question marks the editors left standing.</p>
        @if ($lexicalEntry)
        <div class="home-proof__evidence hourly-gloss">
          @include('book._lexical-entry', [
            'lexicalEntry' => $lexicalEntry,
            'hideComments' => true
          ])
        </div>
        @endif
      </article>

      <article class="home-proof">
        <span class="home-proof__mark" aria-hidden="true">&#10022;</span>
        <h3 class="home-proof__title">Phrases opened out, word by word</h3>
        <p>Real sentences from the legendarium, every word tagged with its inflection — so you
          watch the grammar working, not merely the translation.</p>
        <p class="home-proof__aside">
          <a href="{{ route('sentence.public') }}">Read the @number($noOfSentences) phrases &#8594;</a>
        </p>
      </article>

      <article class="home-proof">
        <span class="home-proof__mark" aria-hidden="true">&#10022;</span>
        <h3 class="home-proof__title">Kept by the people who read it</h3>
        <p>{{ $periodSinceInception }} years of corrections, arguments and additions from readers, linguists and
          translators. Everything reviewed, credited, and reversible.</p>
        <dl class="home-figures">
          <div><dt>@number($noOfLexicalEntries)</dt><dd>glosses</dd></div>
          <div><dt>@number($noOfPosts)</dt><dd>discussions</dd></div>
          <div><dt>@number($noOfThanks)</dt><dd>thanks</dd></div>
        </dl>
      </article>
    </div>
  </section>

  {{-- Today: the serendipity that makes people browse --}}
  <section class="home-section">
    <header class="home-section__header home-section__header--centred">
      <p class="ed-label">Fresh every week</p>
      <h2 class="home-section__title">Today in the book</h2>
      <div class="ed-rule home-section__rule">&#10022;</div>
    </header>

    <div class="home-today">
      @if ($sentence)
      <article class="home-panel home-panel--phrase">
        <p class="ed-label">Phrase of the day</p>
        @include('sentence._random', [
          'sentence' => $sentence,
          'annotationLimit' => 12,
          'clampAfter' => 20
        ])
      </article>
      @endif

      <div class="home-today__aside">
        @if ($lexicalEntry)
        <article class="home-panel">
          <p class="ed-label">Word of the hour</p>
          <p class="home-panel__word">{{ $lexicalEntry->word }}</p>
          <p class="home-panel__gloss">{{ $lexicalEntry->all_glosses }}</p>
          <p class="ed-cite">
            {{ is_object($lexicalEntry->language) ? $lexicalEntry->language->name : '' }}
            @if (! empty($lexicalEntry->source)) &#183; {{ $lexicalEntry->source }} @endif
          </p>
          <p class="home-panel__more">
            <a href="{{ $link->lexicalEntry($lexicalEntry->id) }}">Read the entry &#8594;</a>
          </p>
        </article>
        @endif

        @if (isset($trendingSearches) && count($trendingSearches) > 0)
        <article class="home-panel">
          <p class="ed-label">What others are looking for</p>
          <ul id="popular-searches" class="home-searches">
            @foreach ($trendingSearches as $item)
            <li>
              <a href="{{ $item['url'] }}"
                 data-word="{{ e($item['search_term']) }}"
                 data-language-short-name="{{ e($item['language_short_name'] ?? '') }}">{{ e($item['search_term']) }}</a>
              <span class="ed-ui">@number($item['view_count'])</span>
            </li>
            @endforeach
          </ul>
        </article>
        @endif
      </div>
    </div>
  </section>

  {{-- The reason to come back tomorrow --}}
  <section class="home-section">
    <header class="home-section__header home-section__header--centred">
      <p class="ed-label">Pastimes</p>
      <h2 class="home-section__title">Learn it by playing at it</h2>
      <div class="ed-rule home-section__rule">&#10022;</div>
    </header>

    <div class="home-pastimes">
      {{-- The generator can fall behind, so this section survives having no puzzle. --}}
      @if (! empty($crosswords))
      @php $crossword = $crosswords[0]; @endphp
      <article class="home-panel home-panel--featured">
        <div class="home-panel__banner">
          <p class="ed-label">Weekly crossword</p>
          <span class="ed-ui">{{ \Carbon\Carbon::parse($crossword['date'])->format('j F') }}</span>
        </div>
        <h3 class="home-panel__title">
          {{ $crossword['is_this_week'] ? "This week's" : 'The latest' }} {{ $crossword['title'] }} puzzle
        </h3>
        <p>A fresh grid every week, clued in English and answered in elvish. The whole year is
          there to work back through if you have missed a few.</p>
        <a class="btn btn-primary home-panel__cta"
           href="{{ route('crossword.play', ['languageId' => $crossword['language_id'], 'date' => $crossword['date']]) }}">
          Play this week's crossword
        </a>
        @if (count($crosswords) > 1)
        <p class="ed-ui home-panel__footnote">
          Also in
          @foreach (array_slice($crosswords, 1) as $other)
          <a href="{{ route('crossword.play', ['languageId' => $other['language_id'], 'date' => $other['date']]) }}">{{ $other['title'] }}</a>@if (! $loop->last), @endif
          @endforeach
        </p>
        @endif
      </article>
      @else
      <article class="home-panel">
        <p class="ed-label">Crossword</p>
        <h3 class="home-panel__title">A puzzle a week</h3>
        <p>Grids clued in English and answered in elvish, with a year of them to work back through.</p>
        <p class="home-panel__more"><a href="{{ route('crossword.index') }}">Pick a language &#8594;</a></p>
      </article>
      @endif

      <article class="home-panel">
        <p class="ed-label">Word finder</p>
        <h3 class="home-panel__title">Hunt words in the grid</h3>
        <p>Find hidden elvish words against the clock. Every word you catch links back to its
          entry.</p>
        <p class="home-panel__more">
          <a href="{{ route('word-finder.index') }}">Begin a round &#8594;</a>
        </p>
      </article>

      <article class="home-panel">
        <p class="ed-label">Flashcards &amp; word lists</p>
        <h3 class="home-panel__title">Gather your own vocabulary</h3>
        <p>Keep words as you read into lists of your own, then drill them. Readers here have
          finished @number($noOfFlashcards) cards so far.</p>
        <p class="home-panel__more">
          <a href="{{ route('flashcard') }}">Start a list &#8594;</a>
        </p>
      </article>
    </div>
  </section>

  {{-- The community, and the invitation to join it --}}
  <section class="home-section">
    <div class="home-community">
      <article class="home-panel">
        <div class="home-panel__banner">
          <h3 class="home-panel__title">Most recent community activity</h3>
        </div>
        @include('_shared._audit-trail', [
          'auditTrail' => $auditTrails
        ])
      </article>

      <article class="home-panel">
        <h3 class="home-panel__title">Found a word we are missing?</h3>
        <p>Anyone with an account may propose a gloss or a phrase. A reviewer checks it against
          the source, and your name stays on it thereafter.</p>
        <p class="home-panel__actions">
          <a class="btn btn-primary" href="{{ route('register') }}">Create a free account</a>
          <a class="home-panel__more" href="{{ route('discuss.members') }}">See the contributors &#8594;</a>
        </p>
      </article>
    </div>
  </section>

  @include('_shared._ad', [
    'ad' => 'frontpage'
  ])

  @include('_shared._buy-me-a-coffee', [
    'searchesPerDay' => $searchesPerDay ?? null,
    'periodSinceInception' => $periodSinceInception
  ])

</div>

@endsection
