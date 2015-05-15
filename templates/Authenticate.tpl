<h2>Log in</h2>
{$errorMessage}
<form action="/exec/authenticate.php" method="get">
  <p>Please select an OpenID-provider to use for authenticating yourself. Please note that these providers are only
  used as a means for authentication, and that none of your disclosed personal information will be used.</p>
  
  <p>{foreach from=$providers item=provider}
  <input id="provider-{$provider->id}" type="radio" name="provider" value="{$provider->id}" />
  <label for="provider-{$provider->id}" class="openid-provider" style="background-image:url(img/openid-providers/{$provider->logo})">{$provider->name}</label>
  {/foreach}</p>

  <input type="submit" value="Authenticate" />
</form>
