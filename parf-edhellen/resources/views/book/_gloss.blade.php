@inject('link', 'App\Helpers\LinkHelper')

<blockquote itemscope="itemscope" itemtype="http://schema.org/Article" id="translation-block-{{ $gloss->TranslationID }}"
  @if (!$gloss->Canon)
      class="contribution" 
  @endif>
  <h3 rel="trans-word" class="trans-word">
    @if ((!$gloss->Canon || $gloss->Uncertain) && $gloss->Latest)
    <a href="about.page?browseTo=unverified" title="Unverified or debatable content."><span class="glyphicon glyphicon-question-sign"></span></a>
    @endif
    <span itemprop="headline">
      {{ $gloss->Word }}
    </span>
  </h3> 
  @if ($gloss->Tengwar != null)
  &#32;<span class="tengwar">{{ $gloss->Tengwar }}</span>
  @endif
  @if ($gloss->Type != 'unset')
    <span class="word-type" rel="trans-type">{{ $gloss->Type }}.</span>
  @endif
  <span rel="trans-translation" itemprop="keywords">{{ $gloss->Translation }}</span>

  <p class="word-comments" rel="trans-comments" itemprop="articleBody">{!! $gloss->Comments !!}</p>

  <footer>
    @if (!empty($gloss->Source))
      <span class="word-source" rel="trans-source">[{{ $gloss->Source }}]</span>
    @endif
  
    @if (!empty($gloss->Etymology))
      <span class="word-etymology" rel="trans-etymology">{{ $gloss->Etymology }}.</span>
    @endif
  
    @if ($gloss->TranslationGroupID != null)
      Group: <span itemprop="sourceOrganization">{{ $gloss->TranslationGroup }}</span>.
    @endif
  
    Published <span itemprop="datePublished">{{ $gloss->DateCreated }}</span> by 
    <a href="{{ $link->author($gloss->AuthorID, $gloss->AuthorName) }}" itemprop="author" rel="author" title="View profile for {{ $gloss->AuthorName }}.">{{ $gloss->AuthorName }}</a>
  </footer>
</blockquote>
