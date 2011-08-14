<div id="translation-entry">

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
          <td><input type="text" maxlength="64" size="40" class="rounded-small" name="word" /></td>
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
          <td><input class="rounded-small" type="text" size="40" maxlength="255" name="word" /></td>
        </tr>
        <tr>
          <th scope="row">Gloss</th>
          <td><input class="rounded-small" type="text" size="40" maxlength="255" name="translation" /></td>
        </tr>
        <tr>
          <th scope="row">Etymology</th>
          <td><input class="rounded-small"  type="text" size="40" maxlength="128" name="etymology" /></td>
        </tr>
        <tr>
          <th scope="row">Reference</th>
          <td><input class="rounded-small" type="text" size="40" maxlength="48" name="source" /></td>
        </tr>
        <tr>
          <th scope="row">Phonetic script</th>
          <td><input class="rounded-small"  type="text" size="40" maxlength="128" name="phonetic" /></td>
        </tr>
        <tr>
          <th scope="row">Tengwar</th>
          <td><input class="tengwar rounded-small" type="text" size="20" maxlength="48" name="tengwar" /></td>
        </tr>
        <tr>
          <th colspan="2" scope="col">Comments &amp; Examples</th>
        </tr>
        <tr>
          <td colspan="2"><textarea name="comments" class="rounded-small" rows="4" cols="52"></textarea></td>
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

{* iterate through all translations where the key of the array defines the associated language *}
{foreach from=$translations key=language item=translationsForLanguage}
  <h2>{$language}</h2>
  {* Iterate through each entry for the specificed language *}
  {foreach $translationsForLanguage as $translation}
  <blockquote>
    <h3 rel="trans-word">{$translation->word}</h3>
    {if $translation->tengwar != null}<span class="tengwar">{$translation->tengwar}</span>{/if}
    <span class="word-type" rel="trans-type">{$translation->type}.</span> 
    <span rel="trans-translation">{$translation->translation}</span>

    <p class="word-comments" rel="trans-comments">{$translation->comments}</p>

    {* Only bother with references if such are defined, as they are put within brackets *}
    {if $translation->source != null}<span class="word-source" rel="trans-source">[{$translation->source}]</span>{/if}

    <span class="word-etymology" rel="trans-etymology">{$translation->etymology}</span>

    {if $loggedIn == true}
      {*<a class="feature-link" href="#" onclick="return LANGDict.deleteTranslation({$translation->id})">Delete</a>*}
      <a class="feature-link" href="#" onclick="return LANGDict.showTranslationForm({$translation->id})">Revise</a>
    {/if}
  </blockquote>
  {/foreach}
{/foreach}


{if $indexes|@count > 0}
  <h2>Keywords</h2>
  {foreach $indexes as $index}
    <span class="keyword" rel="keyword-{$index->id}">{$index->word} ({$index->id}) <a href="#" onclick="return LANGDict.removeIndex({$index->id})">x</a></span>
  {/foreach}
{/if}

</div>

{* Side bar with information concerning the word itself such as revisioning, contributions and more *}
<div id="sidebar-entry">
  {if $loggedIn == true}
  <h2>Contribute</h2>
  <ul>
    <li><a href="#" onclick="return LANGDict.showIndexForm('{addslashes($term)}')">Add keyword</a></li>
    <li><a href="#" onclick="return LANGDict.showTranslationForm('{addslashes($term)}')">Add gloss</a></li>
  </ul>
  {/if}
  <h2>Revisions</h2>
  <div class="scroll-view">
  {foreach $revisions as $rev}
    <p>
      {$rev->DateCreated} [{$rev->TranslationID}]<br />
      Gloss: {$rev->Key}<br />
      Author: <a href="profile.php?authorID={$rev->AuthorID}" rel="revision-author">{$rev->AuthorName}</a><br />
      {if $rev->Latest}
        <em>Latest revision</em>
      {else}
        {if $loggedIn == true}<a href="#" onclick="return LANGDict.showTranslationForm({$rev->TranslationID})">Examine revision</a>{/if}
      {/if}
    </p>
  {/foreach}
  </div>
</div>