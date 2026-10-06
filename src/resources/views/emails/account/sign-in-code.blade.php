@component('mail::message')
Someone is signing in to {{ config('app.name') }} with the {{ $providerName }} account **{{ $nickname }}**, which is linked to your account. To finish signing in, enter this code on the page that asked for it:

@component('mail::panel')
# {{ $code }}
@endcomponent

The code works for {{ $lifetime }} minutes, and only in the browser that asked for it.

**If this wasn't you, ignore this e-mail and don't share the code with anyone.** Nobody can sign in without it. We will never ask you for it.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
