@inject('cookie', 'App\Helpers\CookieHelper')

@if ($cookie->hasUserConsent('analytics'))
<script async src="https://www.googletagmanager.com/gtag/js?id=G-6J3WM5JEVV"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', 'G-6J3WM5JEVV');
window.addEventListener('ednavigate-entity',function(ev){
gtag('config','G-6J3WM5JEVV',{'page_path':ev.detail.address});  
gtag('event','page_view');
});
</script>
<script>
(adsbygoogle = window.adsbygoogle||[]).push({
google_ad_client:'ca-pub-8268364504414566',
enable_page_level_ads:true
});
</script>
@endif
