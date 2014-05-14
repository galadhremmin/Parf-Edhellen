<h2>Log in</h2>
{$errorMessage}
<form action="?authenticate" method="post">
  <p>Please select an OpenID-provider to use for authenticating yourself. Please note that these providers are only
  used as a means for authentication, and that none of your disclosed personal information will be used.</p>
  
  <p>Temporarily disabled while we review current contributions. This service will become available again soon!</p>
  {*
  <p>{foreach from=$providers item=provider}
  <input id="provider-{$provider->ProviderID}" type="radio" name="provider" value="{$provider->ProviderID}" />
  <label for="provider-{$provider->ProviderID}" class="openid-provider" style="background-image:url(img/openid-providers/{$provider->Logo})">{$provider->Name}</label>
  {/foreach}</p>

  <input type="submit" value="Authenticate" />
  *}
</form>