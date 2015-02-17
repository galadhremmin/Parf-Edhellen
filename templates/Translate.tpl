<div id="translation-entry">
{* iterate through all translations where the key of the array defines the associated language. The counter 
   uniquely identifies each translation entry, so that the user might levelage this information while navigating
   the results *}
{counter start=-1 print=false}
<div class="row">
  {foreach from=$translations key=language item=translationsForLanguage}
  <article class="col-sm-{$maxColumnWidth} col-md-{$midColumnWidth} col-lg-{$minColumnWidth}">
    <header>
      <h2 rel="language-box">
      {$language} 
      {if isset($languages[$language]) && !is_null($languages[$language]->Tengwar)}
      <span class="tengwar">{$languages[$language]->Tengwar}</span>
      {/if}
      </h2>
    </header>
    <section class="language-box" id="language-box-{$language}">
    {* Iterate through each entry for the specificed language *}
    {foreach $translationsForLanguage as $translation}
    <blockquote itemscope="itemscope" itemtype="http://schema.org/Article" id="translation-block-{counter}" {if $translation->owner < 1}class="contribution"{/if}>
      <h3 rel="trans-word" class="trans-word" itemprop="about">
        {if $translation->owner === 0}
        <a href="about.page?browseTo=unverified" title="This gloss originated from an outdated, unverified or debatable source."><span class="glyphicon glyphicon-question-sign"></span></a>
        {/if}
        {$translation->word}
      </h3> 
      {if $translation->tengwar != null}
      &#32;<span class="tengwar">{$translation->tengwar}</span>
      {elseif $language eq 'Noldorin' or $language eq 'Sindarin'}
      <!--&#32;<a class="tengwar" href="about.page?browseTo=tengwar">{strip_tags($translation->word)}</a> -->
      {/if}
      {if $translation->type != 'unset'}<span class="word-type" rel="trans-type">{$translation->type}.</span>{/if}
      <span rel="trans-translation" itemprop="keywords">{$translation->translation}</span>

      <p class="word-comments" rel="trans-comments" itemprop="articleBody">{$translation->comments}</p>

      {if $translation->owner === 0}
      <section class="alert alert-warning" itemprop="comment">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        Unverified or debatable content. Added by 
        <a href="/profile.page?authorID={$translation->authorID}" itemprop="author" rel="author" title="View profile for {$translation->authorName}.">{$translation->authorName}</a>.
      </section>
      {/if}

      {* Only bother with references if such are defined, as they are put within brackets *}
      <footer>
        {if $translation->source != null}<span class="word-source" rel="trans-source">[{$translation->source}]</span>{/if}

        <span class="word-etymology" rel="trans-etymology">{$translation->etymology}</span>
        (<a href="/profile.page?authorID={$translation->authorID}" itemprop="author" rel="author" title="View profile for {$translation->authorName}.">{$translation->authorName}</a>)
        
        {if $loggedIn == true && ($translation->owner < 1 || $translation->owner == $accountID)}
          {*<a class="feature-link" href="#" onclick="return LANGDict.deleteTranslation({$translation->id})">Delete</a>*}
          <a class="feature-link" href="#" onclick="return LANGDict.showTranslationForm({$translation->id})">Revise</a>
        {/if}
      </footer>
    </blockquote>
    {/foreach}
    </section>
  </article>
  {/foreach}
</div>

{* Show a message if no such word exists *}
{if $namespaces|@count < 1}
  <p><b>{$term}</b> doesn't exist in the dictionary. If you believe it is missing, please
  contribute to make <em>Parf Edhellen</em> more complete!</p>
  {if $loggedIn == true}
  <p class="center"><input type="button" class="rounded-small" value="Create Sense" onclick="return LANGDict.showForm(0)" /></p>
  {/if}
{elseif $translations == null}
  <p>Unfortunately, no one has yet translated <b>{$term}</b>. If you believe you know the
  translation, please make <em>Parf Edhellen</em> more complete by contributing.</p>
  {if $loggedIn == true}
    <p class="center">
      <input type="button" class="rounded-small" value="Add Gloss for {$term}" onclick="LANGDict.showTranslationForm('{addslashes($term)}')" />
    </p>
  {/if}
{/if}

  <div id="row">
    <div class="col-sm-4">
      <h2>Revisions</h2>
      <div class="content scroll-view">
      {foreach $revisions as $rev}
        <p class="no-margin-top">
          {$rev->DateCreated} [{$rev->TranslationID}]<br />
          Gloss: {$rev->Key}<br />
          Author: <a href="profile.page?authorID={$rev->AuthorID}" rel="revision-author">{$rev->AuthorName}</a><br />
          {if $rev->Latest}
            <em>Latest revision</em>
          {else}
            {if $loggedIn == true}<a href="#" onclick="return LANGDict.showTranslationForm({$rev->TranslationID})">Examine revision</a>{/if}
          {/if}
        </p>
      {/foreach}
      </div>
      <hr class="visible-xs">
    </div>
    <div class="col-sm-4">
      <h2>Keywords</h2>
      {if $indexes|@count > 0}
      {foreach $indexes as $index}
        <a href="#{urlencode($index)}"><span class="keyword">{$index}</span></a>
      {/foreach}
      {else}
      There are no additional keywords.
      {/if}
      <hr class="visible-xs">
    </div>
    <div class="col-sm-4 hidden-xs">
      <h2>Related books</h2>
      <div id="related-books"></div>
    </div>
  </div>
</div>
