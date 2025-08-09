@inject('cookie', 'App\Helpers\CookieHelper')

<script src="@assetpath(ads.js)"></script>

@if ($cookie->hasUserConsent('advertising'))
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
@endif
