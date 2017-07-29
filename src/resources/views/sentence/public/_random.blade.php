@inject('link', 'App\Helpers\LinkHelper')
@inject('combiner', 'App\Helpers\SentenceHelper')

<blockquote class="daily-sentence">
  <p class="tengwar tengwar-lg">
    {{ $combiner->combine($sentenceData['fragments'], $sentenceData['tengwar']) }}
  </p>
  <p>
    <em>
    {{ $combiner->combine($sentenceData['fragments'], $sentenceData['latin']) }}
    </em>
  </p>
  <p>{{$sentence->description}}</p>
  <footer>
    {{$sentence->language->name}}
    [{{$sentence->source}}]
    @if ($sentence->account_id)
    by
    <a href="{{ $link->author($sentence->account_id, $sentence->account->nickname) }}">
      {{ $sentence->account->nickname }}
    </a>
    @endif
  </footer>
  @include('sentence.public._readmore', [ 
    'languageId'   => $sentence->language_id,
    'languageName' => $sentence->language->name,
    'sentenceId'   => $sentence->id,
    'sentenceName' => $sentence->name
  ])
</blockquote>
