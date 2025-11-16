@if (config('ed.recaptcha.sitekey'))
<script src="https://www.google.com/recaptcha/enterprise.js?render={{ config('ed.recaptcha.sitekey') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  let f = document.getElementById('register-form');
  if (f) f.addEventListener('submit', function(event) {
    event.preventDefault();
    grecaptcha.enterprise.ready(async () => {
      grecaptcha.enterprise.execute("{{ config('ed.recaptcha.sitekey') }}", {action: 'REGISTER'}).then(function(token) {
        document.getElementById('recaptcha-token').value = token;
        document.getElementById('register-form').submit();
      });
    });
  });
  let idps = Array.from(document.getElementsByClassName('ed-authorize-idp') || []);
  idps.map(e => e.addEventListener('click', function(event) {
    event.preventDefault();
    const url = event.currentTarget.href;
    grecaptcha.enterprise.ready(async () => {
      grecaptcha.enterprise.execute("{{ config('ed.recaptcha.sitekey') }}", {action: 'LOGIN'}).then(function(token) {
        window.location = `${url}?recaptcha_token=${encodeURIComponent(token)}`;
      }).catch(function(error) {
        alert('Recaptcha error - are you a bot? Message: ' + error.message);
      });
    });
  }));
});
</script>
@endif
