@inject('link', 'App\Helpers\LinkHelper')
@if ($parentLexicalEntry)
<p>
  <span class="TextIcon TextIcon--info-sign"></span>
  This is a proposed modification of the lexical entry <a href="{{ $link->lexicalEntry($parentLexicalEntry) }}">{{ $parentLexicalEntry }}</a>.
</p>
@endif

<div class="card">
  <div class="card-body">
    @foreach ($sections as $section)
      @foreach ($section['entities'] as $lexicalEntry)
        @include('book._lexical-entry', [ 
          'lexicalEntry' => $lexicalEntry, 
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
