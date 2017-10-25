<div class="well">
  <h2>{{ $sentence->name }}</h2>
  @include('sentence.public._random', [ 
    'sentence'     => $sentence,
    'sentenceData' => $sentenceData
  ])
</div>
