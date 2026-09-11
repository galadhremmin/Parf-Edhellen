@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', $sentence['sentence']->name . ' (' . $language->name.')')
@section('description', !empty($sentence['sentence']->description)
    ? \Illuminate\Support\Str::limit(strip_tags($sentence['sentence']->description), 155)
    : __('sentence.description.sentence', ['name' => $sentence['sentence']->name, 'language' => $language->name]))
@section('body-class', 'phrase-page')
@section('body')

  {!! Breadcrumbs::render('sentence.public.sentence', $language->id, $language->name,
      $sentence['sentence']->id, $sentence['sentence']->name) !!}

  @if ($sentence['sentence']->is_neologism)
    @include('_shared._neologism', ['account' => $sentence['sentence']->account])
  @endif

  {{-- The phrase is what this page is about, so the phrase is the h1. It used to sit as an h2
       beneath a generic "Phrases" heading, which spent the page's one strongest signal on the
       section rather than on the text. --}}
  <header class="phrase-masthead">
    <p class="ed-label">{{ $language->name }} &nbsp;&#10022;&nbsp; phrase</p>
    <h1 class="phrase-masthead__title">{{ $sentence['sentence']->name }}</h1>
    <div class="ed-rule phrase-masthead__rule" aria-hidden="true">&#10022;</div>
    @if (! empty($sentence['sentence']->description))
    <p class="phrase-masthead__lead">{{ $sentence['sentence']->description }}</p>
    @endif
  </header>

  @ssr('sentence-inspector', ['sentence' => $sentence], [
    'element' => 'div',
    'attributes' => [
      'id' => 'ed-fragment-navigator'
    ]
  ])

  <div class="phrase-aftermatter">

    {{-- The essay follows the text. It used to precede it, which put several hundred words of
         commentary between the reader and the thing they came for. --}}
    @if (! empty($sentence['sentence']->long_description))
    <section class="phrase-essay">
      <h2 class="phrase-essay__title">About this phrase</h2>
      @markdown($sentence['sentence']->long_description)
    </section>
    @endif

    {{-- Provenance stated rather than mumbled: the front page argues that every word carries its
         source, and the phrase page should keep that promise. --}}
    <dl class="phrase-colophon">
      <div>
        <dt class="ed-label">Source</dt>
        <dd>{{ $sentence['sentence']->source }}</dd>
      </div>
      <div>
        <dt class="ed-label">Language</dt>
        <dd><a href="{{ $link->sentencesByLanguage($language->id, $language->name) }}">{{ $language->name }}</a></dd>
      </div>
      @if ($sentence['sentence']->account)
      <div>
        <dt class="ed-label">Contributed by</dt>
        <dd>
          <a href="{{ $link->author($sentence['sentence']->account->id, $sentence['sentence']->account->nickname) }}">
            {{ $sentence['sentence']->account->nickname }}
          </a>
        </dd>
      </div>
      @endif
      <div>
        <dt class="ed-label">Published</dt>
        <dd>@date($sentence['sentence']->created_at)</dd>
      </div>
      @if ($sentence['sentence']->updated_at)
      <div>
        <dt class="ed-label">Last edited</dt>
        <dd>@date($sentence['sentence']->updated_at)</dd>
      </div>
      @endif
    </dl>

    @if (Auth::check())
    <p class="phrase-actions">
      @if (Auth::user()->isAdministrator())
      <a href="{{ route('sentence.confirm-destroy', [ 'id' => $sentence['sentence']->id ]) }}" class="btn btn-secondary">
        <span class="TextIcon TextIcon--trash"></span>
        Delete
      </a>
      @endif
      <a href="{{ $link->contributeSentence($sentence['sentence']->id) }}" class="btn btn-secondary">
        <span class="TextIcon TextIcon--edit"></span>
        Propose changes
      </a>
    </p>
    @endif

    {{-- Three doors, where the page used to end at a wall of comments. --}}
    <div class="phrase-onward">
      @if ($previousSentence)
      <article class="phrase-onward__card">
        <p class="ed-label">Previous in {{ $language->name }}</p>
        <h3 class="phrase-onward__title">
          <a href="{{ $link->sentence($language->id, $language->name, $previousSentence->id, $previousSentence->name) }}">
            {{ $previousSentence->name }}
          </a>
        </h3>
        @if (! empty($previousSentence->description))
        <p>{{ \Illuminate\Support\Str::limit(strip_tags($previousSentence->description), 110) }}</p>
        @endif
      </article>
      @endif

      @if ($nextSentence)
      <article class="phrase-onward__card">
        <p class="ed-label">Next in {{ $language->name }}</p>
        <h3 class="phrase-onward__title">
          <a href="{{ $link->sentence($language->id, $language->name, $nextSentence->id, $nextSentence->name) }}">
            {{ $nextSentence->name }}
          </a>
        </h3>
        @if (! empty($nextSentence->description))
        <p>{{ \Illuminate\Support\Str::limit(strip_tags($nextSentence->description), 110) }}</p>
        @endif
      </article>
      @endif

      <article class="phrase-onward__card">
        <p class="ed-label">All of them</p>
        <h3 class="phrase-onward__title">
          <a href="{{ $link->sentencesByLanguage($language->id, $language->name) }}">
            Every phrase in {{ $language->name }}
          </a>
        </h3>
        <p>The whole list, attested texts and neologisms alike.</p>
      </article>
    </div>

  </div>

  <div class="mt-3">
  @include('_shared._ad', [
    'ad' => 'phrases'
  ])
  </div>

  @include('discuss._standalone', [
    'entity_id'   => $sentence['sentence']->id,
    'entity_type' => 'sentence'
  ])
@endsection

@section('styles')
  <link href="@assetpath(style-sentence.css)" rel="stylesheet">
@endsection
