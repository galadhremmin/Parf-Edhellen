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
{*
{if $myProfile == true && $loggedIn == true && $accountAuthor != null}
<h2>Edit Profile</h2>
<form method="post" action="#" data-module="profile" id="profile-details">
<table>
  <tr>
    <td>Nickname in Tengwar</td>
    <td><input id="profile-field-tengwar" type="text" class="tengwar rounded-small" size="20" maxlength="64" value="{$accountAuthor->tengwar}" /> (optional)</td>
  </tr>
  <tr>
    <td>Description (optional)</td>
  </tr>
  <tr>
    <td colspan="2">
      <textarea id="profile-field-description" class="rounded-small" cols="60" rows="10">{$accountAuthor->profile}</textarea>
    </td>
  </tr>
  <tr>
    <td colspan="2" class="center">
      <input name="action" type="submit" value="Save" class="rounded" />
    </td>
  </tr>
</table>
</form>
{/if}
*}
