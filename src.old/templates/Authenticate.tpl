<h2>Log in</h2>
{if !empty($message)}
  <div class="alert alert-danger" role="alert"><strong>Alae!</strong> {$message}</div>
{/if}
<form action="/exec/authenticate.php" method="get">
  <p>
    Please click on one of the services below to log in. They'll confirm that you're the one you say you are.
    You'll have to enter your username and password, but once you've logged in, we won't access your personal information (apart from your name and e-mail address).
  </p>

  <div class="well">
    {foreach from=$providers item=provider}
      <a class="auth-provider" href="/exec/authenticate.php?provider={$provider->id}"><img src="/img/openid-providers/{$provider->logo}" alt="{$provider->name}"></a>
    {/foreach}
  </div>
</form>
