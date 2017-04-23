{if $message === 'auth-existing'}
  <div class="alert alert-success" role="alert"><strong>Mae govannen!</strong> You've now successfully logged in. Beneath is your profile, which you can edit to your heart's content. <a href="/about.page#profile">More information</a></div>
{elseif $message === 'auth-new'}
  <div class="alert alert-success" role="alert"><strong>Mae govannen!</strong> You've now successfully logged in, and welcome to <em>Parf Edhellen</em>! Beneath is your profile, which you can edit to your heart's content. You can also go into <a href="/dashboard.page">your dashboard</a> and review the status of your contributions.</div>
{/if}

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
