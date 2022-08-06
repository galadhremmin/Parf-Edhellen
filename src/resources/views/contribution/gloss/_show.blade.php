@inject('link', 'App\Helpers\LinkHelper')
@if ($parentGloss)
<p>
  <span class="TextIcon TextIcon--info-sign"></span>
  This is a proposed modification of the gloss <a href="{{ $link->gloss($parentGloss) }}">{{ $parentGloss }}</a>.
</p>
@endif

<div class="card">
  <div class="card-body">
    @foreach ($sections as $section)
      @foreach ($section['entities'] as $gloss)
        @include('book._gloss', [ 
          'gloss' => $gloss, 
          'language' => $section['language'],
          'disable_tools' => true
        ])
      @endforeach
      <span class="badge bg-secondary">{{ $section['language']['name'] }}</span>
    @endforeach

    @foreach ($keywords as $keyword) 
      <span class="badge bg-secondary">{{ $keyword }}</span>
    @endforeach
  </div>
</div>
