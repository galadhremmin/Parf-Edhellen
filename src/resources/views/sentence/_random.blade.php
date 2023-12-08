@inject('link', 'App\Helpers\LinkHelper')
@inject('combiner', 'App\Helpers\SentenceHelper')

<blockquote class="daily-sentence">
  <p class="tengwar tengwar-lg">
    {{ $combiner->combine($sentence['sentence_fragments'], $sentence['sentence_transformations']['tengwar']) }}
  </p>
  <p>
    <em>
    {{ $combiner->combine($sentence['sentence_fragments'], $sentence['sentence_transformations']['latin']) }}
    </em>
  </p>
  <p>{{$sentence['sentence']->description}}</p>
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
