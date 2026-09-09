@inject('link', 'App\Helpers\LinkHelper')
@inject('combiner', 'App\Helpers\SentenceHelper')

@php
  // A "phrase" in the corpus can be a sixty-word poem. Where the caller has
  // asked for it, fade the tengwar out part-way down rather than letting one
  // entry run the length of the page -- the cut edge is an invitation to open
  // the phrase in full. Short phrases are left alone: there is nothing to hide.
  $clampThreshold = $clampAfter ?? null;
  $isLongPhrase = $clampThreshold !== null
    && count($sentence['sentence_fragments']) > $clampThreshold;
@endphp

<blockquote class="daily-sentence">
  <p class="tengwar tengwar-lg{{ $isLongPhrase ? ' is-clamped' : '' }}">
    {{ $combiner->combine($sentence['sentence_fragments'], $sentence['sentence_transformations']['tengwar']) }}
  </p>
  <p class="daily-sentence__latin{{ $isLongPhrase ? ' is-clamped' : '' }}">
    <em>
    {{ $combiner->combine($sentence['sentence_fragments'], $sentence['sentence_transformations']['latin']) }}
    </em>
  </p>
  <p>{{$sentence['sentence']->description}}</p>
  @include('sentence._annotations', ['sentence' => $sentence, 'limit' => $annotationLimit ?? null])
  <footer>
    {{$sentence['sentence']->language->name}}
    [{{$sentence['sentence']->source}}]
    @if ($sentence['sentence']->account)
    by
    <a href="{{ $link->author($sentence['sentence']->account->id, $sentence['sentence']->account->nickname) }}">
      {{ $sentence['sentence']->account->nickname }}
    </a>
    @endif
  </footer>
  @include('sentence._readmore', [ 
    'languageId'   => $sentence['sentence']->language->id,
    'languageName' => $sentence['sentence']->language->name,
    'sentenceId'   => $sentence['sentence']->id,
    'sentenceName' => $sentence['sentence']->name
  ])
</blockquote>
