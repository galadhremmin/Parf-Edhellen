<div id="translation-entry">
{* iterate through all translations where the key of the array defines the associated language. The counter 
   uniquely identifies each translation entry, so that the user might levelage this information while navigating
   the results *}
{counter start=-1 print=false}
{foreach from=$translations key=language item=translationsForLanguage}
  <h2 rel="language-box">{$language}</h2>
  <div class="language-box" id="language-box-{$language}">
  {* Iterate through each entry for the specificed language *}
  {foreach $translationsForLanguage as $translation}
  <blockquote itemscope="itemscope" itemtype="http://schema.org/Article" id="translation-block-{counter}" {if $translation->owner < 1}class="contribution"{/if}>
    {if $translation->owner < 1}
    <span class="contribution" itemprop="comment"><u>Unverified</u> third-party contribution by <a href="/profile.page?authorID={$translation->authorID}" itemprop="author" rel="author">{$translation->authorName}</a></span>
    {/if}
    <h3 rel="trans-word" itemprop="about">{$translation->word}</h3> 
    {if $translation->tengwar != null}
    &#32;<span class="tengwar">{$translation->tengwar}</span>
    {elseif $language eq 'Noldorin' or $language eq 'Sindarin'}
    <!--&#32;<a class="tengwar" href="about.page?browseTo=tengwar">{strip_tags($translation->word)}</a> -->
    {/if}
    {if $translation->type != 'unset'}<span class="word-type" rel="trans-type">{$translation->type}.</span>{/if}
    <span rel="trans-translation" itemprop="keywords">{$translation->translation}</span>

    <p class="word-comments" rel="trans-comments" itemprop="articleBody">{$translation->comments}</p>

    {* Only bother with references if such are defined, as they are put within brackets *}
    {if $translation->source != null}<span class="word-source" rel="trans-source">[{$translation->source}]</span>{/if}

    <span class="word-etymology" rel="trans-etymology">{$translation->etymology}</span>
    
    {if $loggedIn == true && ($translation->owner < 1 || $translation->owner == $accountID)}
      {*<a class="feature-link" href="#" onclick="return LANGDict.deleteTranslation({$translation->id})">Delete</a>*}
      <a class="feature-link" href="#" onclick="return LANGDict.showTranslationForm({$translation->id})">Revise</a>
    {/if}
  </blockquote>
  {/foreach}
  </div>
{/foreach}


{if $loggedIn == true}
{* Add / edit word form *}
  <div class="extendable-form" id="extend-form">
    <form method="post" action="#" onsubmit="return LANGDict.saveNamespace(this.identifier.value)">
      <h2>Add Sense</h2>

      <p>
        Sense:&nbsp;
        <input type="text" name="identifier" size="16" maxlength="48" id="word-input" class="rounded-small" />
      </p>

      <input type="submit" value=" Add " class="rounded-small" />
    </form>
  </div>

{* Add keyword / indexes *}
  <div class="extendable-form" id="index-form">
    <form method="post" action="#" onsubmit="return LANGDict.saveIndex(this)">
      <h2>Add Keywords</h2>

      <table>
        <tr>
          <th scope="row">Sense</th>
          <td>{html_options name=namespaceID options=$namespaces}</td>
        </tr>
        <tr>
          <th scope="row">Keyword</th>
          <td><input type="text" maxlength="64" size="60" class="rounded-small" name="word" /></td>
        </tr>
      </table>

      <input type="button" value="Cancel" class="rounded-small" name="cancelAction" onclick="LANGDict.cancelForm()" />
      <input type="submit" value=" Add " class="rounded-small" />
    </form>
  </div>

{* Add / edit translation form *}
  <div class="extendable-form" id="translation-form">
    <form method="post" action="#" onsubmit="return LANGDict.saveTranslation(this)">
      <h2><span rel="function"></span> Gloss for &ldquo;<span rel="word"></span>&rdquo;</h2>
      <table>
        <tr>
          <th scope="row">Language</th> 
          <td>{html_options name=language options=$languages}</td>
        </tr>
        <tr>
          <th scope="row">Function</th>
          <td>{html_options name=type options=$types}</td>
        </tr>
        <tr>
          <th scope="row">Sense</th>
          <td>{html_options name=namespaceID options=$namespaces}</td>
        </tr>
        <tr>
          <th scope="row">Word</th>
          <td><input class="rounded-small" type="text" size="60" maxlength="255" name="word" /></td>
        </tr>
        <tr>
          <th scope="row">Gloss</th>
          <td><input class="rounded-small" type="text" size="60" maxlength="255" name="translation" /></td>
        </tr>
        <tr>
          <th scope="row">Etymology</th>
          <td><input class="rounded-small"  type="text" size="60" maxlength="128" name="etymology" /></td>
        </tr>
        <tr>
          <th scope="row">Reference</th>
          <td><input class="rounded-small" type="text" size="60" maxlength="48" name="source" /></td>
        </tr>
        <tr>
          <th scope="row">Phonetic script</th>
          <td><input class="rounded-small"  type="text" size="60" maxlength="128" name="phonetic" /></td>
        </tr>
        <tr>
          <th scope="row">Tengwar</th>
          <td><input class="tengwar rounded-small" type="text" size="20" maxlength="48" name="tengwar" /></td>
        </tr>
        <tr>
          <th colspan="2" scope="col">Comments &amp; Examples</th>
        </tr>
        <tr>
          <td colspan="2"><textarea name="comments" class="rounded-small" rows="10" cols="75"></textarea></td>
        </tr>
      </table>
      <input type="button" value="Cancel" class="rounded-small" name="cancelAction" onclick="LANGDict.cancelForm()" />
      <input type="submit" value="Save" class="rounded-small" name="action" />
      <input type="hidden" name="id" value="0" />
    </form>
  </div>
{/if}

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

  <div id="additionals">
    <div style="width:33%;float:left">
      <h2>Keywords</h2>
      <div class="content">
      {if $indexes|@count > 0}
      {foreach $indexes as $index}
        <a href="#{urlencode($index)}"><span class="keyword">{$index}</span></a>
      {/foreach}
      {/if}
      </div>
    </div>
    <div style="width:33%;float:left">
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
    </div>
    <div style="width:34%;float:left">
      <h2>Contribute</h2>
      <div class="content">
      {if $loggedIn == true}
      <ul>
        <li><a href="#" onclick="return LANGDict.showIndexForm('{addslashes($term)}')">Add keyword</a></li>
        <li><a href="#" onclick="return LANGDict.showTranslationForm('{addslashes($term)}')">Add gloss</a></li>
      </ul>
      {else}
      Please log in to access these features.
      {/if}
      </div>
      {*html_checkboxes options=$languages name=languageFilter separator='<br />'*}
    </div>
    <div class="clear"></div>
  </div>
</div>
<div class="performance-data">{$timeElapsed} s elapsed.</div>
