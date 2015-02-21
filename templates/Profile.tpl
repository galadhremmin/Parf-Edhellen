{if $myProfile == true}
<div data-module="profile" id="profile-page">
{/if}
<h2>
  {$author->nickname}
  {if $author->tengwar != null}<span class="tengwar header editable" data-editing-type="text" data-editing-class="tengwar" data-editing-propety="tengwar">{$author->tengwar}</span>{/if}
</h2>

{if $author->profile != null}
<div class="editable" data-editing-type="textarea" data-editing-propety="profile">{$author->profile}</div>
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
