{{-- Word-by-word annotations for a phrase.

     SentenceRepository::getSentence() has always returned `inflections` and
     `speeches` alongside the fragments; nothing rendered them, so the site
     fetched its most distinctive piece of data and threw it away. This shows
     it: each word with its part of speech, its inflections, and a link into
     the entry it came from.

     Expects: $sentence (the getSentence() array).
     Optional: $limit (int) — show at most this many words. A "phrase" in the
     corpus can be a sixty-word poem, which buries a landing page, so callers
     that only have a slot for a taste of it should pass a limit. --}}
@inject('link', 'App\Helpers\LinkHelper')

@php
  $annotated = collect($sentence['sentence_fragments'])
    ->filter(fn ($fragment) => ! $fragment->is_linebreak
      && ! empty(trim((string) $fragment->fragment))
      && ($fragment->speech !== null || $fragment->lexical_entry_inflections->isNotEmpty()));

  $limit = $limit ?? null;
  $hiddenCount = $limit !== null ? max(0, $annotated->count() - $limit) : 0;
  if ($hiddenCount > 0) {
      $annotated = $annotated->take($limit);
  }
@endphp

@if ($annotated->isNotEmpty())
<div class="ed-annotations">
  <p class="ed-label ed-annotations__title">Word by word</p>
  <ul class="ed-annotations__list">
    @foreach ($annotated as $fragment)
    @php
      $inflectionNames = $fragment->lexical_entry_inflections
        ->map(fn ($inflection) => $sentence['inflections'][$inflection->inflection_id]->name ?? null)
        ->filter()
        ->unique()
        ->implode(', ');
    @endphp
    <li class="ed-annotations__word">
      @if ($fragment->lexical_entry_id)
      <a class="ed-annotations__form" href="{{ $link->lexicalEntry($fragment->lexical_entry_id) }}">{{ $fragment->fragment }}</a>
      @else
      <span class="ed-annotations__form">{{ $fragment->fragment }}</span>
      @endif
      @if ($fragment->speech)
      <span class="ed-annotations__speech">{{ $fragment->speech->name }}</span>
      @endif
      @if (! empty($inflectionNames))
      <span class="ed-annotations__inflection">{{ $inflectionNames }}</span>
      @endif
    </li>
    @endforeach
  </ul>
  @if ($hiddenCount > 0)
  <p class="ed-annotations__more ed-ui">and {{ $hiddenCount }} more words</p>
  @endif
</div>
@endif
