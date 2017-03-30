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
    <!--
    {if $loggedIn}
        {if $isAdmin}
        <a href="#" class="ed-delete-button" data-translation-id="{$translation->id}" title="Delete this item"><span class="glyphicon glyphicon-trash pull-right" aria-hidden="true"></span></a>
        {/if}
        <a href="/translate-form.page?translationID={$translation->id}" title="Edit this item"><span class="glyphicon glyphicon-pencil pull-right" aria-hidden="true"></span></a>
        <a href="#" class="ed-favourite-button" data-translation-id="{$translation->id}" title="Add to favourites"><span class="glyphicon glyphicon-heart{if !in_array($translation->id, $favourites)}-empty{/if} pull-right" aria-hidden="true"></span></a>
    {/if}
    -->
    @if ($gloss->ExternalLinkFormat != null && $gloss->ExternalID !== null)
      <a href="{{ str_replace('{ExternalID}', $gloss->ExternalID, $gloss->ExternalLinkFormat) }}" class="ed-external-link-button" title="Open on {$gloss->group->Name} (new tab/window)" target="_blank"><span class="glyphicon glyphicon-globe pull-right" aria-hidden="true"></span></a>
    @endif
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
    <a href="/profile.page?authorID={{ $gloss->AuthorID }}" itemprop="author" rel="author" title="View profile for {{ $gloss->AuthorName }}.">{{ $gloss->AuthorName }}</a>
  </footer>
</blockquote>
