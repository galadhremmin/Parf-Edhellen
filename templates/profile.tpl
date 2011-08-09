<h2>
  Account &ldquo;{$author->nickname}&rdquo; 
  {if $author->tengwar != null}<span class="tengwar">{$author->tengwar}</span>{/if}
</h2>
<h3>Information</h3>
<table class="vertical-cells-top">
  <tr>
    <td>Date registered</td>
    <td>{$author->dateRegistered}</td>
  </tr>
  {if $author->profile != null}
  <tr>
    <td>Description</td>
    <td>{$author->profile}</td>
  </tr>
  {/if}
  {if $author->tengwar != null}
  <tr>
    <td>Tengwar markup</td>
    <td>{$author->tengwar}</td>
  </tr>
  {/if}
</table>
<h3>Contributions</h3>
<table>
  <tr>
    <td>New &amp; revised gloss(es)</td>
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
{if $myProfile == true && $loggedIn == true && $accountAuthor != null}
<h2>Edit Profile</h2>
<form method="post" action="#" onsubmit="return LANGDict.saveProfile(this)">
<table>
  <tr>
    <td><a href="about.php#profile">Preferred nickname</a></td>
    <td><input name="nickname" type="text" class="rounded-small" size="32" maxlength="32" value="{$accountAuthor->nickname}" /></td>
  </tr>
  <tr>
    <td><a href="about.php#tengwar">Nickname in Tengwar</a></td>
    <td><input name="tengwar" type="text" class="tengwar rounded-small" size="20" maxlength="64" value="{$accountAuthor->tengwar}" /> (optional)</td>
  </tr>
  <tr>
    <td>Description (optional)</td>
  </tr>
  <tr>
    <td colspan="2">
      <textarea name="profile" class="rounded-small" cols="60" rows="10">{$accountAuthor->profile}</textarea>
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