{if $myProfile == true}
<div data-module="profile" id="profile-page">
{/if}
<h2>
  {$author->nickname}
  <span class="tengwar header editable" data-editing-type="text" data-editing-class="tengwar" data-editing-propety="tengwar">{$author->tengwar}</span>
</h2>

{if $myProfile == true}
<div class="editable" data-editing-type="textarea" data-editing-propety="profile" data-editing-value="{htmlentities($author->profile)}">
{/if}

{$profileHtml}

{if $myProfile == true}
</div>
{/if}

{if $myProfile == true}
</div>
{/if}

<h3>Information</h3>
<table class="table">
  <tr>
    <td>Date registered</td>
    <td>{$author->dateRegistered}</td>
  </tr>
  <tr>
    <td >New &amp; revised gloss(es)</td>
    <td>{$author->translationCount}</td>
  </tr>
  <tr>
    <td>New word(s)</td>
    <td>{$author->wordCount}</td>
  </tr>
  {if $author->translationCount > 0}
  <tr>
    <td>Ratio <em>word</em>/<em>gloss</em></td>
    <td>{round($author->wordCount / $author->translationCount,2)}</td>
  </tr>
  {/if}
</table>
